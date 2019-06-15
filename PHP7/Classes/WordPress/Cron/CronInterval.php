<?php

declare(strict_types = 1);

namespace NSCL\WordPress\Cron;

/**
 * Don't forget to register the interval manually:
 * <pre>
 *     $interval = new CronInterval(...);
 *     ...
 *     $interval->register();
 * </pre>
 */
class CronInterval
{
    protected $name = '';
    protected $interval = PHP_INT_MAX;
    protected $title = '';

    public function __construct(string $name, int $interval = PHP_INT_MAX, string $title = '')
    {
        $this->name     = $name;
        $this->interval = $interval;
        $this->title    = $title;
    }

    public function register()
    {
        add_filter('cron_schedules', [$this, 'cronSchedules']);
    }

    public function cronSchedules(array $schedules): array
    {
        $schedules[$this->name] = [
            'interval' => $this->interval,
            'display'  => $this->title
        ];

        return $schedules;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'interval' => $this->interval,
            'title'    => $this->title
        ];
    }

    public function toSchedule(): array
    {
        return [
            'interval' => $this->interval,
            'display'  => $this->title
        ];
    }

    public static function createFromSchedule(string $name, array $schedule): CronInterval
    {
        $interval = ($schedule['interval'] ?? PHP_INT_MAX);
        $title    = ($schedule['display'] ?? '');

        return new self($name, $interval, $title);
    }
}
