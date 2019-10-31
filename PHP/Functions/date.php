<?php

declare(strict_types = 1);

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

/**
 * @param float $gmt
 * @param bool $addZeroOffset Optional.
 * @return string "UTC", "UTC-0:30", "UTC+2" etc.
 */
function gmt2utc(float $gmt, bool $addZeroOffset = true): string
{
    if ($gmt == 0) {
        return $addZeroOffset ? 'UTC+0' : 'UTC';
    }

    $hours = abs((int)$gmt);

    $minutes = abs($gmt) - $hours;
    $minutes = round($minutes * 4) / 4; // Limit variants to 0, 0.25, 0.5, 0.75 or 1
    $minutes = (int)($minutes * 60); // Only 0, 15, 30, 45 or 60 are possible

    if ($minutes == 60) {
        $hours++;
        $minutes = 0;
    }

    $utc = $gmt >= 0 ? 'UTC+' : 'UTC-';
    $utc .= $hours;

    if ($minutes > 0) {
        $utc .= ':' . $minutes;
    }

    return $utc;
}

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
