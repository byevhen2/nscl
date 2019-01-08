<?php

declare(strict_types = 1);

if (!function_exists('get_current_url')) {
    /**
     * @see https://stackoverflow.com/a/6768831
     */
    function get_current_url(): string
    {
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = 'https://' . $url;
        } else {
            $url = 'http://' . $url;
        }

        return $url;
    }
}
