<?php

declare(strict_types = 1);

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

/**
 * @param string|array $search
 * @param string|array $replace
 * @param string $subject
 * @param int $limit Is unlimited for 0 or negative value.
 * @return string
 */
function str_nreplace($search, $replace, string $subject, int $limit): string
{
    if ($limit <= 0) {
        return str_replace($search, $replace, $subject); // Easy
    }

    // Quote regular expression characters
    $search = is_string($search) ? preg_quote_common($search) : array_map('preg_quote_common', $search);

    // Impossible to use multiple replacements with single search string
    if (is_array($replace) && is_string($search)) {
        $replace = (string)reset($replace);
    }

    if (is_string($replace)) {
        $pattern = '/' . ( is_string($search) ? $search : implode('|', $search) ) . '/';
        return preg_replace($pattern, $replace, $subject, $limit);

    } else {
        // Get rid of custom keys
        $search = array_values($search);
        $replace = array_values($replace);

        $result = $subject;

        // Make replacements
        for ($i = 0, $count = count($search); $i < $count; $i++) {
            $searchString = $search[$i]; // All strings already preg_quote'd
            $replaceString = $replace[$i] ?? ''; // Default behavior with ''

            $pattern = '/' . $searchString . '/';

            $result = preg_replace($pattern, $replaceString, $result, $limit);
        }

        return $result;
    }
}

/**
 * Add number starting from the most right position of the string. For example:
 * <i>strradd("0.12", 125) = "1.37"</i>
 *
 * @param string $str
 * @param int $add
 * @return string
 */
function str_radd(string $str, int $add = 1): string
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

/**
 * Variant of str_nreplace() with 1 as the limit.
 *
 * @param string|array $search
 * @param string|array $replace
 * @param string $subject
 * @return string
 */
function str_replace_once($search, $replace, string $subject): string
{
    return str_nreplace($search, $replace, $subject, 1);
}

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

/**
 * Convert string into boolean value.
 *
 * @param string $string
 * @return bool
 */
function str_to_bool(string $string): bool
{
    if (in_array($string, ['false', 'off', 'no', 'disable', 'disabled'])) {
        return false;
    } else {
        return boolval($string);
    }
}

/**
 * @param string $subject
 * @param string|array $prefix One or more prefixes.
 * @return string
 */
function str_unprefix(string $subject, $prefix): string
{
    if (is_string($prefix)) {
        if (strpos($subject, $prefix) === 0) {
            return substr($subject, strlen($prefix));
        }
    } else {
        foreach ($prefix as $anotherPrefix) {
            if (strpos($subject, $anotherPrefix) === 0) {
                // Make single replacement only
                return substr($subject, strlen($anotherPrefix));
            }
        }
    }

    // No such prefix in the string
    return $subject;
}
