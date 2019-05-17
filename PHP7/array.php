<?php

/*
 * Functions:
 *     aimplodef
 *     array_disjunction
 *     array_insert_after
 *     array_length
 *     array_remove
 *     array_wrap
 *     as_array
 *     first_key
 *     implodef
 *     is_assoc_array
 *     is_numeric_array
 *     is_numeric_natural_array
 *     keys
 *     keys_and_values
 *     last_key
 *     values
 */

declare(strict_types = 1);

if (!function_exists('aimplodef')) {
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
}

if (!function_exists('array_disjunction')) {
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
}

if (!function_exists('array_insert_after')) {
    /**
     * Add an array after the specified key in the associative array.
     *
     * @param mixed $searchKey
     * @param array $insert Array to insert.
     * @param array $array Subject array.
     * @return array Result array with inserted items.
     */
    function array_insert_after($searchKey, array $insert, array $array)
    {
        $index = array_search($searchKey, array_keys($array));

        if ($index !== false) {
            $result = array_slice($array, 0, $index + 1, true)
                + $insert
                + array_slice($array, $index + 1, count($array), true);
        } else {
            $result = $array + $insert;
        }

        return $result;
    }
}

if (!function_exists('array_length')) {
    function array_length($var): int
    {
        return is_array($var) ? count($var) : 0;
    }
}

if (!function_exists('array_remove')) {
    /**
     * Remove value from the array.
     *
     * @param array $array
     * @param mixed $value
     * @param bool $resetIndexes Optional. <b>false</b> by default.
     * @return array
     */
    function array_remove(array $array, $value, bool $resetIndexes = false): array
    {
        $index = array_search($value, $array);

        if ($index !== false) {
            unset($array[$index]);
        }

        return ($resetIndexes) ? array_values($array) : $array;
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap(array $array, string $wrapper)
    {
        return array_map(function ($value) use ($wrapper) {
            return $wrapper . $value . $wrapper;
        }, $array);
    }
}

if (!function_exists('as_array')) {
    /**
     * Also note function is_countable() in PHP 7.3.
     *
     * @param mixed $value
     * @return ArrayIterator
     */
    function as_array($value)
    {
        if (is_scalar($value)) {
            $value = [$value];
        }

        return new ArrayIterator($value);
    }
}

if (!function_exists('first_key')) {
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
}

if (!function_exists('implodef')) {
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
}

if (!function_exists('is_assoc_array')) {
    function is_assoc_array(array $array)
    {
        return !is_numeric_array($array);
    }
}

if (!function_exists('is_numeric_array')) {
    function is_numeric_array(array $array): bool
    {
        if (empty($array)) {
            return true;
        }

        $keys = array_keys($array);
        $numericCount = array_filter($keys, 'is_numeric');

        return ($numericCount == count($array));
    }
}

if (!function_exists('is_numeric_natural_array')) {
    function is_numeric_natural_array(array $array): bool
    {
        foreach (array_keys($array) as $index => $key) {
            if ($index !== $key) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('keys')) {
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
}

if (!function_exists('keys_and_values')) {
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
}

if (!function_exists('last_key')) {
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
}

if (!function_exists('values')) {
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
}
