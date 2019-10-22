<?php

// PHP_INT_MIN available only since PHP 7
if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}
