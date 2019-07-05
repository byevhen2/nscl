<?php

declare(strict_types = 1);

/**
 * @param string $property
 * @param mixed $default
 * @return mixed
 */
function _get(string $property, $default = '')
{
    // Will return $default if the value is Null
    return $_GET[$property] ?? $default;
}

/**
 * @param string $property
 * @param mixed $default
 * @return mixed
 */
function _post(string $property, $default = '')
{
    // Will return $default if the value is Null
    return $_POST[$property] ?? $default;
}

/**
 * @param string $property
 * @param mixed $default
 * @return mixed
 */
function _request(string $property, $default = '')
{
    // Will return $default if the value is Null
    return $_REQUEST[$property] ?? $default;
}
