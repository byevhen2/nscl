<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Cron;

class CronInterval
{
    public $id       = '';
    public $interval = 12 * HOUR_IN_SECONDS; // "twicedaily"
    public $display  = '';

    public function __construct(string $id, string $interval, string $display = '')
    {
        $this->id       = $id;
        $this->interval = $interval;
        $this->display  = $display;

        add_filter('cron_schedules', [$this, 'registerInterval']);
    }

    public function registerInterval(array $schedules): array
    {
        $schedules[$this->id] = [
            'interval' => $this->interval,
            'display'  => $this->display
        ];

        return $schedules;
    }
}
