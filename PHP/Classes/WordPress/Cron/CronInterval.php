<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Cron;

class CronInterval
{
    public $id       = '';
    public $interval = 12 * HOUR_IN_SECONDS; // "twicedaily"
    public $display  = '';

    /**
     * @param string $id
     * @param int $interval The amount of seconds between each call.
     * @param string $display Optional.
     */
    public function __construct(string $id, int $interval, string $display = '')
    {
        $this->id       = $id;
        $this->interval = $interval;
        $this->display  = $display;

        add_filter('cron_schedules', [$this, 'registerInterval']);
    }

    /**
     * Callback for filter "cron_schedules".
     *
     * @param array $schedules
     * @return array
     */
    public function registerInterval(array $schedules): array
    {
        $schedules[$this->id] = [
            'interval' => $this->interval,
            'display'  => $this->display
        ];

        return $schedules;
    }
}
