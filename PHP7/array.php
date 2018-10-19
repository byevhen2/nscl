<?php

declare(strict_types = 1);

/**
 * Join array elements (keys and values) with a string, previously
 * formatting the value.
 *
 * @param string $glue Glue for values, equal to implode() function.
 * @param array $items
 * @param string $format Output format for key=>value pairs.
 * @return string
 */
function aimplodef(string $glue, array $pieces, string $format = '%1$s => %2$s'): string
{
    array_walk($pieces, function (&$value, $key) use ($format) {
        $value = sprintf($format, $key, $item);
    });

    return implode($glue, $pieces);
}

/**
 * Computes the exclusive disjunction (difference of arrays).
 *
 * @param array $a First array to compare.
 * @param array $b Second array to compare.
 * @param array $_ Optional. More arrays to compare.
 * @return array Difference of arrays.
 */
function array_disjunction(array $a, array $b, array $_ = null): array
{
    if (is_null($_)) {
        return array_merge(array_diff($a, $b), array_diff($b, $a));

    } else {
        $arrays = func_get_args();
        $diffs  = [];

        // Make diffs for each element
        foreach ($arrays as $index => $array) {
            $check = $arrays;

            if ($index > 0) {
                // Move $index'th element to the beginning of an array
                unset($check[$index]);
                array_unshift($check, $arrays[$index]);
            }

            $diffs[] = call_user_func_array('array_diff', $check);
        }

        return call_user_func_array('array_merge', $diffs);
    }
}

function array_length($var): int
{
    return is_array($var) ? count($var) : 0;
}

/**
 * Also note function is_countable() in PHP 7.3.
 *
 * @param mixed $value
 * @return \ArrayIterator
 */
function as_array($value)
{
    if (is_scalar($value)) {
        $value = [$value];
    }

    return new ArrayIterator($value);
}

/**
 * @param array $array
 * @return mixed|false The value of the first key or FALSE if the array is empty.
 *
 * @deprecated since PHP 7.3
 * @see array_key_first()
 */
function first_key($array)
{
    // array_keys() + reset() is faster way than using foreach cycle, especially
    // on big arrays
    $keys = array_keys($array);
    return reset($keys);
}

/**
 * Join array elements with a string, previously formatting the value.
 *
 * @param string $glue Glue for values, equal to implode() function.
 * @param array $items
 * @param string $format Output format for values, applied before imploding.
 * @return string
 */
function implodef(string $glue, array $pieces, string $format = '%s'): string
{
    if ($format != '%s') {
        $pieces = array_map(function ($value) {
            return sprintf($format, $value);
        }, $pieces);
    }

    return implode($glue, $pieces);
}

function is_assoc_array(array $array)
{
    return !is_numeric_array($array);
}

function is_numeric_array(array $array): bool
{
    if (empty($array)) {
        return true;
    }

    $keys = array_keys($array);
    $numericCount = array_filter($keys, 'is_numeric');

    return ($numericCount == count($array));
}

function keys(array $array, bool $skipNumeric = true): array
{
    $keys = [];

    foreach ($array as $key => $value) {
        if (!is_numeric($key) || !$skipNumeric) {
            $keys[] = $key;
        }

        if (is_array($value)) {
            $keys = array_merge($keys, keys($value, $skipNumeric));
        }
    }

    $keys = array_unique($keys);
    $keys = array_values($keys); // Get rid of custom keys from array_unique()

    return $keys;
}

function keys_and_values(array $array, bool $skipNumericKeys = true): array
{
    $items = [];

    foreach ($array as $key => $value) {
        if (!is_numeric($key) || !$skipNumericKeys) {
            $items[] = $key;
        }

        if (!is_array($value)) {
            $items[] = $value;
        } else {
            $items = array_merge($items, keys_and_values($value, $skipNumericKeys));
        }
    }

    $items = array_unique($items);
    $items = array_values($items);

    return $items;
}

/**
 * @param array $array
 * @return mixed|false The value of the last key or FALSE if the array is empty.
 *
 * @deprecated since PHP 7.3
 * @see array_key_last()
 */
function last_key($array)
{
    // array_keys() + end() is faster way than using foreach cycle, especially
    // on big arrays
    $keys = array_keys($array);
    return end($keys);
}

/**
 * @param array $array
 * @return array All values of the multidimensional array.
 */
function values(array $array): array
{
    $values = [];

    array_walk_recursive($array, function ($value) use (&$values) {
        $values[] = $value;
    });

    $values = array_unique($values);
    $values = array_values($values); // Get rid of custom keys from array_unique()

    return $values;
}
