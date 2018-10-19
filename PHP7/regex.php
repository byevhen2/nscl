<?php

declare(strict_types = 1);

if (!function_exists('regex_combine')) {
    /**
     * Combine results of two subpatterns into single array.
     *
     * @param string $pattern
     * @param string $subject
     * @param int $keyIndex If there are no such index in matches then the result
     *                      will be a numeric array with appropriate values.
     * @param int $valueIndex If there are no such index in matches then the result
     *                        will be an array with appropriate keys but with empty
     *                        values (empty strings "").
     * @return array
     */
    function regex_combine(string $pattern, string $subject, int $keyIndex = -1, int $valueIndex = 0): array
    {
        $count  = (int)preg_match_all($pattern, $subject, $matches);
        $keys   = ($matches[$keyIndex] ?? []);
        $values = ($matches[$valueIndex] ?? array_fill(0, $count, ''));

        if (!empty($values) && !empty($keys)) {
            return array_combine($keys, $values);
        } else {
            // Only $keys can be empty at this point (because we used array_fill()
            // for values)
            return $values;
        }
    }
}

if (!function_exists('regex_match')) {
    /**
     * Searches for a value in the subject string by passed pattern.
     *
     * @param string $pattern
     * @param string $subject
     * @param mixed $default Return value if nothing found.
     * @param int $index The index of the result group.
     * @return mixed The matched or default value.
     */
    function regex_match(string $pattern, string $subject, $default = '', int $index = 0)
    {
        preg_match($pattern, $subject, $matches);
        return ($matches[$index] ?? $default);
    }
}

if (!function_exists('regex_match_all')) {
    /**
     * Searches all values in the subject string by passed pattern.
     *
     * @param string $pattern
     * @param string $subject
     * @param mixed $default Return value if nothing found.
     * @param int $index The index of the result group.
     * @return mixed
     */
    function regex_match_all(string $pattern, string $subject, $default = [], int $index = 0)
    {
        preg_match_all($pattern, $subject, $matches);
        return ($matches[$index] ?? $default);
    }
}

if (!function_exists('regex_test')) {
    /**
     * @param string $pattern
     * @param string $subject
     * @param int $index The index of the result group.
     * @return bool
     */
    function regex_test(string $pattern, string $subject, int $index = 0): bool
    {
        $found = preg_match($pattern, $subject, $matches);
        return ($found && isset($matches[$index]));
    }
}
