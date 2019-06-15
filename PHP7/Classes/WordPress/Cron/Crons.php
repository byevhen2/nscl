<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Cron;

use NSCL\WordPress\Cron\Cron;
use NSCL\WordPress\Cron\CronInterval;

class Crons
{
    /**
     * @param string $name Cron name.
     * @param callable $callback Function to call on cron.
     * @param string|array $interval Registered interval name (like "hourly",
     *                               "twicedaily", "daily" or custom interval)
     *                               or custom interval data to register:
     *                               ["name", "interval", "title"].
     * @param array $arguments Arguments to pass to the hook function.
     */
    public static function create(string $name, callable $callback, $interval = 'daily', array $arguments = []): Cron
    {
        if (is_string($interval)) {
            $schedules = wp_get_schedules();
            $schedule  = ($schedules[$interval] ?? []);

            $intervalObj = CronInterval::createFromSchedule($interval, $schedule);

        } else if (isset($interval['name'], $interval['interval'], $interval['title'])) {
            $intervalObj = new CronInterval($interval['name'], $interval['interval'], $interval['title']);
            $intervalObj->register();

        } else {
            $intervalObj = new CronInterval('');
        }

        $cronObj = new Cron($name, $callback, $intervalObj, $arguments);

        return $cronObj;
    }
}
