<?php

declare(strict_types = 1);

if (!function_exists('decimals_count')) {
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
}

if (!function_exists('format_size')) {
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
}

if (!function_exists('format_size_si')) {
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
}

if (!function_exists('remove_prefixes')) {
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
}

if (!function_exists('slash')) {
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
}

if (!function_exists('slash_left')) {
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
}

if (!function_exists('unslash')) {
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
}

if (!function_exists('unslash_left')) {
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
}

if (!function_exists('wrap_with')) {
    /**
     * @param string|array $subject
     * @param string $wrapper
     * @return string|array
     */
    function wrap_with($subject, string $wrapper)
    {
        if (is_string($subject)) {
            return $wrapper . $subject . $wrapper;
        } else {
            return array_map(function ($string) use ($wrapper) {
                return "{$wrapper}{$string}{$wrapper}";
            }, $subject);
        }
    }
}
