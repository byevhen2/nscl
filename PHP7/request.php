<?php

declare(strict_types = 1);

if (!function_exists('_get')) {
    /**
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    function _get(string $property, $default = '')
    {
        return (isset($_GET[$property])) ? $_GET[$property] : $default;
    }
}

if (!function_exists('_post')) {
    /**
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    function _post(string $property, $default = '')
    {
        return (isset($_POST[$property])) ? $_POST[$property] : $default;
    }
}
