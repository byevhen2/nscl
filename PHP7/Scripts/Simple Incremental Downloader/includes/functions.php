<?php

declare(strict_types = 1);
namespace sid;

function say(string $message, bool $ln = true)
{
    global $enableLogs, $logFile;

    if ($ln) {
        $message .= PHP_EOL;
    }

    echo $message;

    if ($enableLogs) {
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}

function format_time(float $time): string
{
    return number_format($time, 3, '.', '');
}
