<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Cron;

if (!class_exists(__NAMESPACE__ . '\Cron')) {

    /**
     * Don't forget to schedule the event manually:
     * <pre>
     *     $cron = new Cron(...);
     *     ...
     *     $cron->schedule();
     * </pre>
     */
    class Cron
    {
        protected $name = '';
        protected $callback = null;
        protected $interval = null;
        protected $arguments = [];

        public function __construct(string $name, callable $callback, CronInterval $interval, array $arguments = [])
        {
            $this->name      = $name;
            $this->callback  = $callback;
            $this->interval  = $interval;
            $this->arguments = $arguments;

            // Register hook for callback
            add_action($this->name, $this->callback, 10, count($arguments));
        }

        public function schedule()
        {
            if (!$this->isScheduled()) {
                wp_schedule_event(time(), $this->interval->getName(), $this->name, $this->arguments);
            }
        }

        public function scheduleAt(int $timestamp)
        {
            if (!$this->isScheduled()) {
                wp_schedule_event($timestamp, $this->interval->getName(), $this->name, $this->arguments);
            }
        }

        public function unschedule()
        {
            $nextTime = wp_next_scheduled($this->name);
            wp_unschedule_event($nextTime, $this->name);
        }

        public function isScheduled(): bool
        {
            return (bool)wp_next_scheduled($this->name);
        }

        public function nextTime(): int
        {
            $nextTime = wp_next_scheduled($this->name);

            if ($nextTime !== false) {
                return intval($nextTime);
            } else {
                return (time() - 1); // A second before
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

        public function getInterval(): CronInterval
        {
            return $this->getInterval();
        }
    }

}
