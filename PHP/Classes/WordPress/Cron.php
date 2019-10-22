<?php

declare(strict_types = 1);

namespace NSCL\WordPress;

class Cron
{
    public $action    = 'basic_cron';
    public $interval  = 'daily';
    /** @var int Interval duration in seconds. */
    public $duration  = 86400;
    public $label     = '';
    public $callback  = null;
    /** @var int Action priority. */
    public $priority  = 10;
    /** @var array Arguments to pass to the hook's callback function. */
    public $arguments = [];

    /**
     * @param string $name
     * @param callable $callback
     * @param array $args Optional.
     * @param string $args['interval'] Pass to use existing interval.
     * @param int $args['duration'] Interval duration in <b>seconds</b>. Pass to
     *     create new custom interval (has bigger priority than parameter
     *     "interval").
     * @param string $args['label'] Interval label. Applied only to custom
     *     intervals.
     * @param int $args['priority'] Action priority.
     * @param array $args['arguments'] Arguments to pass to the hook's callback
     *     function.
     */
    public function __construct(string $name, callable $callback, array $args = [])
    {
        $this->action    = "{$name}_cron";
        $this->callback  = $callback;
        $this->label     = $args['label'] ?? $this->label;
        $this->priority  = $args['priority'] ?? $this->priority;
        $this->arguments = $args['arguments'] ?? $this->arguments;

        $addInterval     = isset($args['duration']);
        $schedulesFilter = $addInterval ? 'addInterval' : 'fetchDuration';

        add_filter('cron_schedules', [$this, $schedulesFilter]);

        if ($addInterval) {
            $this->interval = $args['interval'] ?? "{$name}_cron_interval";
            $this->duration = $args['duration'];
        } else {
            $this->interval = $args['interval'] ?? $this->interval;
        }

        // Register action for callback
        add_action($this->action, $this->callback, $this->priority, count($this->arguments));
    }

    /**
     * @param array $intervals
     * @return array
     */
    public function addInterval(array $intervals): array
    {
        $intervals[$this->interval] = [
            'interval' => $this->duration,
            'display'  => $this->label
        ];

        return $intervals;
    }

    /**
     * Get duration from existing interval.
     *
     * @param array $intervals
     * @return array
     */
    public function fetchDuration(array $intervals): array
    {
        if (isset($intervals[$this->interval]['interval'])) {
            $this->duration = $intervals[$this->interval]['interval'];
        }

        return $intervals;
    }

    /**
     * @return bool
     */
    public function schedule(): bool
    {
        return $this->scheduleAt(time());
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function scheduleAt(int $timestamp): bool
    {
        if (!$this->isScheduled()) {
            return wp_schedule_event($timestamp, $this->interval, $this->action, $this->arguments);
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function unschedule(): bool
    {
        $timestamp = wp_next_scheduled($this->action);

        if ($timestamp !== false) {
            return wp_unschedule_event($timestamp, $this->action);
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function isScheduled(): bool
    {
        return wp_next_scheduled($this->action) !== false;
    }

    /**
     * @return int
     */
    public function nextTime(): int
    {
        $timestamp = wp_next_scheduled($this->action);

        if ($timestamp !== false) {
            return intval($timestamp);
        } else {
            return (time() - 1); // A second before
        }
    }

    /**
     * @return int How many seconds left before cron starts again.
     */
    public function timeLeft(): int
    {
        $nextTime = $this->nextTime();
        $currentTime = time();

        if ($nextTime > $currentTime) {
            return $nextTime - $currentTime;
        } else {
            return 0;
        }
    }

    public function run()
    {
        if (empty($this->arguments)) {
            call_user_func($this->callback);
        } else {
            call_user_func_array($this->callback, $this->arguments);
        }
    }
}
