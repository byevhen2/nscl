<?php

declare(strict_types = 1);

function decimals_count(float $number): int
{
    // 14 - the approximate maximum number of decimal digits in PHP
    $string  = number_format($number, 14, '.', '');
    $decimal = strstr($string, '.');

    if ($decimal === false) {
        // The $number is integer and does not have a decimal part
        return 0;
    }

    // Remove comma
    $decimal = substr($decimal, 1);
    // Trim strings like "557.07000000000005" or "557.05999999999995"
    $decimal = preg_replace('/0+\d$|9+\d$/m', '', $decimal);
    // Trim ending zeros
    $decimal = rtrim($decimal, '0');

    return strlen($decimal);
}

function format_size(int $size, string $unit = null): string
{
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    $base  = 0;
    $kilo  = 1024;

    if (is_null($unit) || !in_array($unit, $units)) {
        $base = floor(log($size, $kilo));
    } else {
        $base = array_search($unit, $units);
    }

    $sizeString = round($size / pow($kilo, $base), 2);
    $sizeString .= ' ' . $units[$base];

    return $sizeString;
}

function format_size_si(int $size, string $unit = null): string
{
    $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $base  = 0;
    $kilo  = 1000;

    if (is_null($unit) || !in_array($unit, $units)) {
        $base = floor(log($size, $kilo));
    } else {
        $base = array_search($unit, $units);
    }

    $sizeString = round($size / pow($kilo, $base), 2);
    $sizeString .= ' ' . $units[$base];

    return $sizeString;
}

/**
 * @param int|string $value
 * @return int The number in range [0; oo)
 */
function posint($value): int
{
    return max(0, intval($value));
}

function remove_prefixes(array $strings, string $prefix): array
{
    return array_map(function (string $string) use ($prefix) {
        if (strpos($string, $prefix) === 0) {
            return substr($string, strlen($prefix));
        } else {
            return $string;
        }
    }, $strings);
}

/**
 * Add trailing slash ("/") to the end of the URI.
 *
 * @param string $uri
 * @return string
 */
function slash(string $uri): string
{
    return unslash($uri) . '/';
}

/**
 * Add slash ("/") in the beginning of the URI.
 *
 * @param string $uri
 * @return string
 */
function slash_left(string $uri): string
{
    return '/' . unslash_left($uri);
}

function trim_decimal_zeros(string $number, string $decimalSeparator = '.'): string
{
    $separator = preg_quote($decimalSeparator);

    $number = preg_replace("/{$separator}0++$/", '', $number);
    $number = preg_replace("/({$separator}[^0]++)0++$/", '$1', $number);

    return $number;
}

/**
 * Remove trailing slash ("/") from the end of the URI.
 *
 * @param string $uri
 * @return string
 */
function unslash(string $uri): string
{
    return rtrim($uri, '\/');
}

/**
 * Remove slash ("/") from the beginning of the URI.
 *
 * @param string $uri
 * @return string
 */
function unslash_left(string $uri): string
{
    return ltrim($uri, '\/');
}

/**
 * @param string|array $var
 * @param string $before
 * @param string $after Optional. If omitted then $before will be used.
 * @return string|array
 */
function wrap($var, string $before, string? $after = null)
{
    if (is_null($after)) {
        $after = $before;
    }

    if (!is_array($var)) {
        return $before . $var . $after;
    } else {
        return array_map(function ($item) use ($before, $after) {
            return $before . $item . $after;
        }, $var);
    }
}
