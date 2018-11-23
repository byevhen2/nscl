<?php

declare(strict_types = 1);

if (!function_exists('calc_nights')) {
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return int The number of days. On PHP 5.3- and old machines may always
     *             return 6015 days - it's a bug of PHP.
     */
    function calc_nights(DateTime $startDate, DateTime $endDate): int
    {
        $from = clone $startDate;
        $to   = clone $endDate;

        // Set the same time for both dates
        $from->setTime(0, 0, 0);
        $to->setTime(0, 0, 0);

        $diff = $from->diff($to);

        return (int)$diff->format('%r%a');
    }
}

if (!function_exists('current_date_with_time')) {
    /**
     * @param string $time Time in 24-hour format.
     * @return DateTime
     */
    function current_date_with_time(string $time): DateTime
    {
        $hands = parse_time($time);

        // On PHP 7.1+: list('hours' => $hours, 'minutes' => $minutes) = $hands;
        $hours   = $hands['hours'];
        $minutes = $hands['minutes'];

        $date = new DateTime();
        $date->setTime($hours, $minutes);

        return $date;
    }
}

if (!function_exists('next_timestamp_with_time')) {
    /**
     * @param string $time Time in 24-hour format.
     * @return int Next timestamp with a specified time.
     */
    function next_timestamp_with_time(string $time): int
    {
        $nextDate = current_date_with_time($time);
        $nextTime = (int)$nextDate->format('U') + 59; // Till HH:MM:59

        $currentTime = time();

        $secondsLeft = $nextTime - $currentTime;

        if ($nextTime >= $currentTime) {
            return $nextTime;
        } else {
            return $nextTime + 86400; // Seconds in a day
        }
    }
}

if (!function_exists('parse_time')) {
    /**
     * @param string $time Time in 24-hour format.
     * @return array ["hours", "minutes"]
     */
    function parse_time(string $time): array
    {
        $matched = (bool)preg_match('/^(?<hours>[01][0-9]|2[0-3]):(?<minutes>[0-5][0-9])/', $time, $hands);

        $hours   = ($matched ? intval($hands['hours']) : 0);
        $minutes = ($matched ? intval($hands['minutes']) : 0);

        return ['hours' => $hours, 'minutes' => $minutes];
    }
}
