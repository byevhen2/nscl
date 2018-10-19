<?php

declare(strict_types = 1);

if (!function_exists('str2bool')) {
    /**
     * Convert string into boolean value.
     *
     * @param string $string
     * @return bool
     */
    function str2bool(string $string): bool
    {
        if (in_array($string, ['false', 'off', 'no', 'disable', 'disabled'])) {
            return false;
        } else {
            return boolval($string);
        }
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     *
     * @author MrHus
     * @link http://stackoverflow.com/a/834355/3918377
     */
    function str_ends_with(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        if ($length == 0) {
            return true; // Notice: the length of $needle is 0, not the length of $haystack
        } else {
            $ending = substr($haystack, -$length);
            return ($ending === $needle);
        }
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     *
     * @author MrHus
     * @link http://stackoverflow.com/a/834355/3918377
     */
    function str_starts_with(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}

if (!function_exists('strradd')) {
    /**
     * Add number starting from the most right position of the string. For example:
     * <i>strradd("0.12", 125) = "1.37"</i>
     *
     * @param string $str
     * @param int $add
     * @return string
     */
    function strradd(string $str, int $add = 1): string
    {
        for ($i = strlen($str) - 1; $i >= 0; $i--) {
            if ($str[$i] == '.' || $str[$i] == ',') {
                continue;
            }

            $sum  = (int)$str[$i] + $add;
            $tens = floor($sum / 10);

            $str[$i] = $sum - ($tens * 10);

            $add = $tens;

            if ($add == 0) {
                break;
            }
        }

        if ($add != 0) {
            $str = $add . $str;
        }

        return $str;
    }
}
