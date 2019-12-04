<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Cron;

class CronTask
{
    public $prefix = '';
    public $name   = 'basic';
    public $action = 'basic_cron'; // "{prefix}{name}_cron"

    public $interval = 'twicedaily';

    public $priority = 10;

    public $callback = null;
    public $arguments = [];

    /**
     * @param string $name
     * @param string $interval Optional. "twicedaily" by default.
     * @param int $priority Optional. 10 by default.
     */
    public function __construct(string $name, string $interval = 'twicedaily', int $priority = 10)
    {
        $this->name     = $name;
        $this->action   = "{$this->prefix}{$this->name}_cron";
        $this->interval = $interval;
        $this->priority = $priority;

        add_action($this->action, [$this, 'run'], $this->priority);
    }

    /**
     * @param callable $callback
     * @param array $arguments Optional.
     */
    public function setCallback(callable $callback, array $arguments = [])
    {
        if (is_null($this->callback)) {
            remove_action($this->action, [$this, 'run'], $this->priority);
        } else {
            remove_action($this->action, $this->callback, $this->priority);
        }

        $this->callback = $callback;
        $this->arguments = $arguments;

        add_action($this->action, $callback, $this->priority, count($arguments));
    }

    /**
     * Override this method or call manually.
     */
    public function run()
    {
        if (!is_null($this->callback)) {
            if (empty($this->arguments)) {
                call_user_func($this->callback);
            } else {
                call_user_func_array($this->callback, $this->arguments);
            }
        }
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
     * @return int|false
     */
    public function scheduledAt()
    {
        return wp_next_scheduled($this->action);
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
}
