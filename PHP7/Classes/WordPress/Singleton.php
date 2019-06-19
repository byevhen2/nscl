<?php

declare(strict_types = 1);

namespace NSCL\WordPress;

final class Singleton
{
    /** @var self */
    private static $instance = null;

    private function __construct() {}

    public function __clone()
    {
        $this->terminate(__FUNCTION__, __('Do not clone the Singleton class.', 'todebug'), '1.0');
    }

    /**
     * Unserializing the object.
     */
    public function __wakeup()
    {
        $this->terminate(__FUNCTION__, __('Do not clone the Singleton class.', 'todebug'), '1.0');
    }

    private function terminate(string $function, string $message, string $version)
    {
        if (function_exists('_doing_it_wrong')) {
            _doing_it_wrong($function, $message, $version);
        } else {
            die($message);
        }
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
