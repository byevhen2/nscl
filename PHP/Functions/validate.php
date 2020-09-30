<?php

declare(strict_types = 1);

/**
 * @param float $number The number to test.
 * @param int $precision Optional. 2 by default.
 * @return bool
 */
function is_float_zero(float $number, int $precision = 2): bool
{
    return round($number, $precision) == 0;
}

/**
 * @param string $url
 * @return bool
 *
 * @see https://www.w3schools.com/php/filter_validate_url.asp
 */
function is_valid_url(string $url): bool
{
    return (filter_var($url, FILTER_VALIDATE_URL) !== false);
}

function validate_bool($raw): bool
{
    return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
}

function validate_bool_or_string($raw)
{
    if (is_string($raw) && !in_array($raw, ['true', 'yes', '1', 'on', 'false', 'no', '0', 'off'])) {
        return validate_string($raw);
    } else {
        return validate_bool($raw);
    }
}

/**
 * @param mixed $raw The value to convert to ID.
 * @return int The value in range [0; oo).
 */
function validate_id($raw): int
{
    return validate_int($raw, 0);
}

/**
 * @param string|array $raw Comma-separated IDs or an array of IDs.
 * @return array An array of valid IDs (valid - means "no zeros").
 */
function validate_ids($raw): array
{
    if (!is_array($raw)) {
        $raw = explode(',', $raw);
    }

    $ids = [];

    foreach ($raw as $rawId) {
        $id = validate_id($rawId);

        // Filter empty values
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return $ids;
}

/**
 * @param mixed $raw The value to convert to integer.
 * @param int $min Optional. -Infinity by default.
 * @param int $max Optional. Infinity by default.
 * @return int The value in range [$min; $max].
 */
function validate_int($raw, int $min = null, int $max = null): int
{
    // Also convert false (on failure) into int
    $value = (int)filter_var($raw, FILTER_VALIDATE_INT);

    $min = ($min ?? $value);
    $max = ($max ?? $value);

    // $min <= $value <= $max
    return max($min, min($value, $max));
}

/**
 * @param mixed $raw The value to validate.
 * @param string $default Optional. Default order. "DEST" by default.
 * @return string "DEST"|"ASC"
 */
function validate_order($value, string $default = 'DEST'): string
{
    $value = strtoupper($raw);

    if ($value == 'DEST' || $value == 'ASC') {
        return $value;
    } else {
        return $default;
    }
}

/**
 * @param mixed $raw The value to validate.
 * @param string $default Optional. Default relation. "OR" by default.
 * @return string "OR"|"AND"
 */
function validate_relation($raw, string $default = 'OR'): string
{
    $value = strtoupper($raw);

    if ($value == 'OR' || $value == 'AND') {
        return $value;
    } else {
        return $default;
    }
}

function validate_string($raw)
{
    return filter_var($raw, FILTER_SANITIZE_STRING);
}
