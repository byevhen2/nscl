<?php

/*
 * Functions:
 *     stderr
 *     stdout
 */

declare(strict_types = 1);

if (!function_exists('stderr')) {
    function stderr(string $message)
    {
        fputs(STDERR, $message . PHP_EOL);
    }
}

if (!function_exists('stdout')) {
    function stdout(string $message)
    {
        fputs(STDOUT, $message . PHP_EOL);
    }
}
