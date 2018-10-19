<?php

declare(strict_types = 1);

class Script
{
    /** @var bool */
    protected $isShortinit = false;

    /** @var string */
    protected $abspath = '';

    public function __construct(array $config = [])
    {
        $this->isShortinit = (bool)($config['shortinit'] ?? $this->isShortinit);
        $this->abspath = ($this->isShortinit) ? ($config['abspath'] ?? $this->abspath) : ABSPATH;

        // Add trailing slash to "abspath"
        if (!empty($this->abspath)) {
            $this->abspath = rtrim($this->abspath, '\/') . '/';
        } else {
            $this->abspath = './';
        }

        // Enable short init
        if ($this->isShortinit && !defined('SHORTINIT')) {
            define('SHORTINIT', true);
        }

        // Load WordPress
        $loadFile = $this->abspath . 'wp-load.php';

        if (file_exists($loadFile)) {
            require_once $loadFile;
        }
    }

    /**
     * @param string $user
     * @param string $password
     * @param string $name
     * @param string $host
     * @return \wpdb|null
     */
    public function connect(string $user, string $password, string $name, string $host = 'localhost')
    {
        $wpdb = new class($user, $password, $name, $host) extends \wpdb {
            // Change default value of $allowBail from TRUE to FALSE and always
            // suppress errors outputting
            /**
             * @param bool $allowBail Show error messages (HTML) and stop
             *                        current script via wp_die() function with
             *                        database connection error.
             */
            public function db_connect(bool $allowBail = false)
            {
                parent::db_connect($allowBail);
            }

            // Add public getter for private field
            public function is_connected(): bool
            {
                return $this->has_connected;
            }
        };

        return ($wpdb->is_connected() ? $wpdb : null);
    }

    /**
     * Use options that holds required data, instead of pass parameters directly
     * into <b>wpdb</b> object.
     *
     * @param string $user
     * @param string $password
     * @param string $name
     * @param string $host
     * @return \wpdb|null
     */
    public function connectByOptions(string $user, string $password, string $name, string $host)
    {
        $user     = get_option($user);
        $password = get_option($password);
        $name     = get_option($name);
        $host     = get_option($host);

        return $this->connect($user, $password, $name, $host);
    }

    /**
     * @param string $user
     * @param string $password
     * @param string $name
     * @param string $host
     * @return \wpdb
     * @throws \RuntimeException
     */
    public function tryConnect(string $user, string $password, string $name, string $host = 'localhost'): \wpdb
    {
        $wpdb = $this->connect($user, $password, $name, $host);

        if (is_null($wpdb)) {
            throw new \RuntimeException('Error establishing a database connection');
        }

        return $wpdb;
    }

    /**
     * Use options that holds required data, instead of pass parameters directly
     * into <b>wpdb</b> object.
     *
     * @param string $user
     * @param string $password
     * @param string $name
     * @param string $host
     * @return \wpdb
     * @throws \RuntimeException
     */
    public function tryConnectByOptions(string $user, string $password, string $name, string $host): \wpdb
    {
        $wpdb = $this->connectByOptions($user, $password, $name, $host);

        if (is_null($wpdb)) {
            throw new \RuntimeException('Error establishing a database connection');
        }

        return $wpdb;
    }
}
