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

/**
 * Add an array after the specified position.
 *
 * @param array $array Subject array.
 * @param int $position
 * @param array $insert Array to insert.
 * @return array Result array with inserted items.
 */
function array_insert_after(array $array, int $position, array $insert): array
{
    if ($position < 0) {
        return array_merge($insert, $array);
    } else if ($position >= count($array)) {
        return array_merge($array, $insert);
    } else {
        return array_merge(
            array_slice($array, 0, $position + 1, true),
            $insert,
            array_slice($array, $position + 1, count($array), true)
        );
    }
}

/**
 * Add an array after the specified key in the associative array.
 *
 * @param array $array Subject array.
 * @param mixed $searchKey
 * @param array $insert Array to insert.
 * @return array Result array with inserted items.
 */
function array_insert_after_key(array $array, $searchKey, array $insert): array
{
    $position = array_search($searchKey, array_keys($array));

    if ($position !== false) {
        return array_insert_after($array, $position, $insert);
    } else {
        return array_insert_after($array, count($array), $insert);
    }
}

function array_length($var): int
{
    return is_array($var) ? count($var) : 0;
}

/**
 * Move an array element to a new index. Moves the element before the existing
 * item $array[$to].
 *
 * Use on numeric arrays only.
 *
 * @param array $array
 * @param int $from
 * @param int $to
 */
function array_move(array &$array, int $from, int $to)
{
    if ($from == $to) {
        return;
    }

    $item = array_splice($array, $from, 1);

    // The array become shorter and all indexed after $from decreased
    if ($from < $to) {
        $to--;
    }

    array_splice($array, $to, 0, $item);
}

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

/**
 * @param array $array
 * @param string $before
 * @param string $after Optional. If omitted then $before will be used.
 * @return array
 */
function array_wrap(array $array, string $before, string? $after = null): array
{
    if (is_null($after)) {
        $after = $before;
    }

    return array_map(function ($item) use ($before, $after) {
        return $before . $item . $after;
    }, $array);
}

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

/**
 * @param string $string
 * @return array
 */
function explode_by_spaces(string $string): array
{
    $string = trim($string);

    if ($string !== '') {
        return preg_split('/\s+/', $string);
    } else {
        return [];
    }
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
 * @param string $glue
 * @param array $pieces
 * @return string "A, B and C"
 */
function implode_and(string $glue, array $pieces)
{
    $piecesCount = count($pieces);

    if ($piecesCount > 1) {
        $lastMasterpiece = array_pop($pieces);
        $pieces[$piecesCount - 2] .= ' and ' . $lastMasterpiece;
    }

    return implode($glue, $pieces);
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

function is_numeric_natural_array(array $array): bool
{
    foreach (array_keys($array) as $index => $key) {
        if ($index !== $key) {
            return false;
        }
    }

    return true;
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
 * @return array
 */
function mirror_values(array $array): array
{
    return array_combine($array, $array);
}

/**
 * Unlike array_unique() this function resets all indexes.
 *
 * @param array $array
 * @return array
 */
function unique_values(array $array): array
{
    return array_values(array_unique($array));
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
