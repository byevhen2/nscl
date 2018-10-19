<?php

declare(strict_types = 1);

if (!function_exists('is_valid_url')) {
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
}
