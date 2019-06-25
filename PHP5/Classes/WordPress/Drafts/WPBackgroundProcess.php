<?php

/*
 * WordPress background processing tool.
 *
 * Requirements:
 *     minimum      - PHP 5.3,   WordPress 3.0;
 *     recommended  - PHP 7.0,   WordPress 4.2;
 *     tested up to - PHP 7.3.6, WordPress 5.2.2.
 */

namespace NSCL\WordPress\Drafts;

class WPBackgroundProcess
{
    /** @var string Action prefix. */
    protected $prefix = 'wpbg';

    /**
     * @var string Action name. The length should be 35 symbols (or less if the
     * prefix is bigger). The length of option name is limited in 64 characters.
     *
     * Option name will consist of:
     *     (5) prefix "wpbg" with separator "_"
     *     (35 <=) action name of background process
     *     (5) lock option suffix "_lock"
     *     (19) WP's transient prefix "_transient_timeout_"
     */
    protected $action = 'process';

    /** @var string Process name. Prefix + "_" + action name. */
    protected $name;

    /** @var string Cron hook name. */
    protected $cronHook;

    /** @var string Cron interval name. */
    protected $cronInterval;

    /** @var int Cron interval duration in minutes. */
    protected $cronDuration = 5;

    /** @var int Start time of current process. */
    protected $startTime = 0;

    /**
     * @var int Lock for a short amount of time.
     *
     * Don't lock for too long. The process allowed to work for an hour. But we
     * should use the short time for locks. If the process fail with an error on
     * some task then the progress will freeze for too long.
     *
     * @see WPBackgroundProcess::limitLockTime()
     */
    protected $lockTime = 30;

    /**
     * @var int How many time do we have before the process will be terminated.
     * Measured in seconds.
     *
     * @see WPBackgroundProcess::limitExecutionTime()
     */
    protected $executionTime = 30;

    /**
     * @var int Maximum allowed execution time.
     *
     * @see WPBackgroundProcess::limitExecutionTime()
     */
    protected $maxExecutionTime = \HOUR_IN_SECONDS;

    /**
     * @var int The number of seconds before the execution time limit to stop
     * the process and not fall dawn with an error.
     */
    protected $timeReserve = 10;

    /**
     * @var int The maximum amount of available memory (in bytes).
     *
     * @see WPBackgroundProcess::limitMemory()
     */
    protected $memoryLimit = 2147483648; // 2 GiB

    /**
     * @var float Limit in range (0; 1). Prevents exceeding {memoryFactor}% of
     * max memory.
     */
    protected $memoryFactor = 0.9; // 90% of max memory

    /** @var int How many tasks will have each of the batches. */
    protected $batchSize = 500;

    /** @var int How many new tasks were returned by task() method. */
    protected $tasksReturned = 0;

    /** @var boolean */
    protected $isAborting = false;

    public function __construct()
    {
        $this->name = $this->prefix . '_' . $this->action; // "wpbg_process"

        $this->cronHook = $this->name . '_cron';
        $this->cronInterval = $this->name . '_cron_interval';
    }

    public function listenEvents()
    {
        // Listen for AJAX calls
        add_action('wp_ajax_' . $this->name, array($this, 'maybeHandle'));
        add_action('wp_ajax_nopriv_' . $this->name, array($this, 'maybeHandle'));

        // Listen for cron calls
        add_action($this->cronHook, array($this, 'maybeHandle'));
        add_filter('cron_schedules', array($this, 'addInterval'));
    }

    /**
     * @param array $tasks
     * @return self
     */
    public function addTasks($tasks)
    {
        if (empty($tasks)) {
            return $this;
        }

        $batches = array_chunk($tasks, $this->batchSize);

        // Add batches to database
        foreach ($batches as $batch) {
            // "wpbg_process_batch_bf46955b5005c1893583b64c0ff440be"
            $key = $this->nextBatchKey();

            update_option($key, $batch, 'no');
        }

        // Update counts
        $this->increaseBatchesTotalCount(count($batches));
        $this->increaseTasksTotalCount(count($tasks));

        return $this;
    }

    /**
     * Run the background process.
     *
     * @return \WP_Error|array The response or WP_Error on failure.
     */
    public function run()
    {
        // Run healthchecking cron
        $this->scheduleCron();

        // Dispatch event
        $ur = add_query_arg($this->queryArgs(), $this->queryUrl());
        $args = $this->postArgs();

        // Use AJAX handle (see listenAjax())
        return wp_remote_post(esc_url_raw($url), $args);
    }

    /**
     * Re-run the process if it's down.
     */
    public function touch()
    {
        if (!$this->isRunning() && !$this->isQueueEmpty()) {
            // The process is down. Don't wait for the cron, restart the process
            $this->run();
        }
    }

    public function cancel()
    {
        if ($this->isRunning()) {
            update_option($this->name . '_abort', true, 'no');
        } else {
            $this->unscheduleCron();
            $this->removeBatches();
        }
    }

    /**
     * @return string
     */
    protected function queryUrl()
    {
        return admin_url('admin-ajax.php');
    }

    /**
     * @return array
     */
    protected function queryArgs()
    {
        return array(
            'action' => $this->name,
            'nonce'  => wp_create_nonce($this->name)
        );
    }

    /**
     * @return array The arguments for wp_remote_post().
     */
    protected function postArgs()
    {
        return array(
            'timeout'   => 0.01,
            'blocking'  => false,
            'data'      => array(),
            'cookies'   => $_COOKIE,
            'sslverify' => apply_filters('verify_local_ssl', false)
        );
    }

    /**
     * Checks whether data exists within the queue and that the process is not
     * already running.
     */
    public function maybeHandle()
    {
        // Don't lock up other requests while processing
        session_write_close();

        if ($this->isRunning()) {
            // Background process already running
            $this->fireDie();

        } else if ($this->isQueueEmpty()) {
            // No data to process
            $this->fireDie();

        } else {
            // Have something to process
            if ($this->isDoingAjax()) {
                check_ajax_referer($this->name, 'nonce');
            }

            // Lock immediately, before doing all the required calculations. At
            // the moment we can only use the default value for lock time. But
            // later in handle() we will set the proper lock time
            $this->lock();

            // Get limits of execution time, lock time and memory
            $this->limitExecutionTime();
            $this->limitLockTime();
            $this->limitMemory();

            // Start doing tasks
            $this->handle();
        }
    }

    protected function limitExecutionTime()
    {
        $executionTime = (int)ini_get('max_execution_time');

        if ($executionTime === false || $executionTime === '') {
            // Sensible default. A timeout limit of 30 seconds is common on
            // shared hostings
            $executionTime = 30;
        }

        // Try to increase execution time limit
        $disabledFunctions = explode(',', ini_get('disable_functions'));

        if (!in_array('set_time_limit', $disabledFunctions)) {
            if (set_time_limit(0)) {
                // Set to 1 hour
                $executionTime = $this->maxExecutionTime;
            }
        }

        $this->executionTime = $executionTime;
    }

    protected function limitLockTime()
    {
        // The lock time should exceed the execution time
        $lockTime = $this->executionTime + 5;

        // Don't lock the process for too long
        $this->lockTime = min(30, $lockTime);
    }

    protected function limitMemory()
    {
        $memoryLimit = ini_get('memory_limit');

        // The memory is not limited?
        if (!$memoryLimit || $memoryLimit == -1) {
            // Set to 2 GiB
            $memoryLimit = '2048M';
        }

        $this->memoryLimit = intval($memoryLimit) * 1024 * 1024;
    }

    /**
     * Pass each queue item to the task handler, while remaining within server
     * memory and time limit constraints.
     */
    protected function handle()
    {
        do {
            $batch = $this->nextBatch();

            // & will force the foreach cycle to watch for updates in the array
            foreach ($batch->tasks as $index => &$workload) {
                // Continue locking the process
                $this->lock();

                $response = $this->task($workload);

                if ($response === true) {
                    unset($batch->tasks[$index]); // Remove task from the batch
                } else if ($response !== false) {
                    $batch->tasks[] = $response; // Add new task to the batch
                    $this->tasksReturned++;
                }

                $this->taskComplete($workload, $response);

                if ($this->shouldStop()) {
                    // No time or memory left? We need to restart the process
                    break;
                }
            }

            unset($workload);

            // Remove the batch if all the tasks done
            if ($this->isAborting()) {
                $this->removeBatches();

            } else if (empty($batch->tasks)) {
                $this->removeBatch($batch->name);

            } else {
                $this->updateBatch($batch->name, $batch->tasks);

                // Update tasks count
                if ($this->tasksReturned > 0) {
                    $this->increaseTasksTotalCount($this->tasksReturned);
                }
            }

            $this->tasksReturned = 0;

        } while (!$this->shouldStop() && !$this->isQueueEmpty());

        // Unlock the process to restart it
        $this->unlock();

        // Start next batch if not completed yet or complete the process
        if (!$this->isQueueEmpty()) {
            $this->run();
        } else {
            $this->afterComplete();
        }

        $this->fireDie();
    }

    /**
     * Override this method to perform any actions required on each queue item.
     * Return the modified item for further processing in the next pass through.
     * Or, return true to remove the item from the queue.
     *
     * @param mixed $workload
     * @return mixed
     */
    public function task($workload)
    {
        sleep(1);

        return true;
    }

    /**
     * @param mixed $workload
     * @param mixed $response
     */
    protected function taskComplete($workload, $response)
    {
        $this->increaseTasksCompletedCount(1);
    }

    /**
     * Should stop executing tasks and restart the process.
     *
     * @return boolean
     */
    protected function shouldStop()
    {
        return $this->timeExceeded() || $this->memoryExceeded() || $this->isAborting();
    }

    /**
     * Ensures the batch never exceeds a sensible time limit.
     *
     * @return boolean
     */
    protected function timeExceeded()
    {
        $timeLeft = $this->timeLeft();
        return $timeLeft <= $this->timeReserve; // N seconds in reserve
    }

    /**
     * How many seconds left for execution.
     *
     * @return int Raw time left (without time reserve applied).
     */
    protected function timeLeft()
    {
        return $this->startTime + $this->executionTime - time();
    }

    /**
     * @return boolean
     */
    protected function memoryExceeded()
    {
        $currentMemory = memory_get_usage(true);
        $memoryLimit = $this->memoryLimit * $this->memoryFactor;

        return $currentMemory >= $memoryLimit;
    }

    /**
     * @return boolean
     *
     * @global \wpdb $wpdb
     */
    public function isAborting()
    {
        global $wpdb;

        if ($this->isAborting) {
            return true;
        }

        // Get uncached value (the code partly from the function get_option())
        $suppressStatus = $wpdb->suppress_errors(); // Save to restore later

        $query = $wpdb->prepare("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name` = %s LIMIT 1", $this->name . '_abort');
        $row = $wpdb->get_row($query);

        $wpdb->suppress_errors($suppressStatus);

        if (is_object($row)) {
            $this->isAborting = (bool)maybe_unserialize($row->option_value);
        } else {
            $this->isAborting = false;
        }

        return $this->isAborting;
    }

    /**
     * @return boolean
     */
    public function isInProgress()
    {
        return $this->isRunning() || !$this->isQueueEmpty();
    }

    /**
     * @return boolean
     */
    public function isQueueEmpty()
    {
        return $this->getBatchesLeft() == 0;
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        if (get_transient($this->name . '_lock')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Lock the process so that multiple instances can't run simultaneously.
     */
    protected function lock()
    {
        if ($this->startTime == 0) {
            $this->startTime = time();
        }

        set_transient($this->name . '_lock', microtime(), $this->lockTime);
    }

    /**
     * Unlock the process so that other instances can spawn.
     */
    protected function unlock()
    {
        delete_transient($this->name . '_lock');
    }

    protected function afterComplete()
    {
        $this->unscheduleCron();

        if ($this->isAborting()) {
            $this->afterCancel();
        }

        do_action($this->name . '_complete');

        $this->clearOptions();
    }

    protected function afterCancel()
    {
        do_action($this->name . '_cancelled');
    }

    protected function clearOptions()
    {
        delete_option($this->name . '_abort');
        delete_option($this->name . '_batches_count');
        delete_option($this->name . '_tasks_count');
        delete_option($this->name . '_tasks_completed');
    }

    /**
     * Generates a unique key based on microtime. Queue items are given a unique
	 * key so that they can be merged upon save.
     *
     * @param int $length Optional. Length limit of the key. 64 by default
     *     (the maximum length of the option name).
     * @return string The key like "wpbg_process_batch_bf46955b5005c1893583b64c0ff440be".
     */
    protected function nextBatchKey($length = 64)
    {
        $key = md5(microtime() . rand());
        $prepend = $this->name . '_batch_';

        return substr($prepend . $key, 0, $length);
    }

    /**
     * @return \stdClass
     *
     * @global \wpdb $wpdb
     */
    protected function nextBatch()
    {
        global $wpdb;

        $raw = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `option_name` AS name, `option_value` AS data FROM {$wpdb->options} WHERE `option_name` LIKE %s ORDER BY `option_id` ASC LIMIT 1",
                $this->name . '_batch_%'
            )
        );

        $batch = new \stdClass();

        if (!is_null($raw)) {
            $batch->name = $raw->name;
            $batch->tasks = maybe_unserialize($raw->data);
        } else {
            $batch->name = '';
            $batch->tasks = array();
        }

        return $batch;
    }

    /**
     * @param string $name
     * @param array $tasks
     */
    protected function updateBatch($name, $tasks)
    {
        if (!empty($name)) {
            update_option($name, $tasks, 'no');
        }
    }

    /**
     * @param string $name
     */
    protected function removeBatch($name)
    {
        if (!empty($name)) {
            delete_option($name);
        }
    }

    /**
     * @global \wpdb $wpdb
     */
    protected function removeBatches()
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE %s",
                $this->name . '_batch_%'
            )
        );
    }

    /**
     * @return int
     */
    public function getBatchesTotalCount()
    {
        return (int)get_option($this->name . '_batches_count', 0);
    }

    /**
     * @param int $increment
     */
    protected function increaseBatchesTotalCount($increment)
    {
        update_option($this->name . '_batches_count', $this->getBatchesTotalCount() + $increment, 'no');
    }

    /**
     * @return int
     *
     * @global \wpdb $wpdb
     */
    public function getBatchesLeft()
    {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE `option_name` LIKE %s",
                $this->name . '_batch_%'
            )
        );

        return (int)$count;
    }

    /**
     * @param int $precision Optional. 3 digits by default.
     * @return float The progress value in range [0; 100].
     */
    public function getBatchProgress($precision = 3)
    {
        $total = $this->getBatchesTotalCount();

        if ($total > 0) {
            $left = $this->getBatchesLeft();
            $completed = max(0, $total - $left);

            $progress = round($completed / $total * 100, $precision);
            $progress = min($progress, 100);
        } else {
            $progress = 100; // All of nothing done
        }

        return $progress;
    }

    /**
     * @param int $precision Optional. 3 digits by default.
     * @return float The progress value in range [0; 100].
     */
    public function getProgress($precision = 3)
    {
        $total = $this->getTasksTotalCount();

        if ($total > 0) {
            $completed = $this->getTasksCompletedCount();

            $progress = round($completed / $total * 100, $precision);
            $progress = min($progress, 100);
        } else {
            $progress = 100; // All of nothing done
        }

        return $progress;
    }

    /**
     * @return int
     */
    public function getTasksTotalCount()
    {
        return (int)get_option($this->name . '_tasks_count', 0);
    }

    /**
     * @param int $increment
     */
    protected function increaseTasksTotalCount($increment)
    {
        update_option($this->name . '_tasks_count', $this->getTasksTotalCount() + $increment, 'no');
    }

    /**
     * @return int
     */
    public function getTasksCompletedCount()
    {
        return (int)get_option($this->name . '_tasks_completed', 0);
    }

    /**
     * @param int $increment
     */
    protected function increaseTasksCompletedCount($increment)
    {
        update_option($this->name . '_tasks_completed', $this->getTasksCompletedCount() + $increment, 'no');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    protected function scheduleCron()
    {
        if (!wp_next_scheduled($this->cronHook)) {
            wp_schedule_event(time(), $this->cronInterval, $this->cronHook);
        }
    }

    protected function unscheduleCron()
    {
        $timestamp = wp_next_scheduled($this->cronHook);

        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->cronHook);
        }
    }

    public function addInterval($schedules)
    {
        $schedules[$this->cronInterval] = array(
            'interval' => \MINUTE_IN_SECONDS * $this->cronDuration,
            'display'  => sprintf(__('Every %d Minutes'), $this->cronDuration)
        );

        return $schedules;
    }

    /**
     * @return self
     */
    public function basicAuth()
    {
        add_filter('http_request_args', function ($request) {
            $request['headers']['Authorization'] = 'Basic ' . base64_encode(USERNAME . ':' . PASSWORD);
            return $request;
        });

        return $this;
    }

    protected function fireDie()
    {
        if ($this->isDoingAjax()) {
            wp_die();
        } else {
            exit(0); // Don't call wp_die() on cron
        }
    }

    protected function isDoingAjax()
    {
        if (function_exists('wp_doing_ajax')) {
            return wp_doing_ajax();
        } else {
            return defined('DOING_AJAX') && DOING_AJAX;
        }
    }
}
