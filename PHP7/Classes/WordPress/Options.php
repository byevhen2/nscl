<?php

declare(strict_types = 1);

namespace NSCL\WordPress;

if (!class_exists(__NAMESPACE__ . '\Options')) {

    class Options
    {
        /** @var string */
        protected $prefix = '';

        public function __construct(string $prefix = '')
        {
            $this->prefix = $prefix;
        }

        /**
         * @param string $option Option name without prefix.
         * @return string Option name with prefix.
         */
        public function prefix(string $option): string
        {
            return $this->prefix . $option;
        }

        /**
         * @param string $option Option name without prefix.
         * @param mixed $default Optional. <b>false</b> by default.
         * @return mixed Option value or default value.
         */
        public function get(string $option, $default = false)
        {
            $option = $this->prefix($option);
            return get_option($option, $default);
        }

        /**
         * @global \wpdb $wpdb
         *
         * @param string $option Option name without prefix.
         * @param mixed $default Optional. <b>false</b> by default.
         * @return mixed Option value or default value.
         *
         * @see get_uncached_option()
         */
        public function getUncached(string $option, $default = false)
        {
            global $wpdb;

            $option = $this->prefix($option);

            // The code partly from function get_option()
            $suppressStatus = $wpdb->suppress_errors(); // Set to suppress errors and
                                                        // save the previous value

            $query = $wpdb->prepare("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name` = %s LIMIT 1", $option);
            $row   = $wpdb->get_row($query);

            $wpdb->supress_errors($suppressStatus);

            if (is_object($row)) {
                return maybe_unserialize($row->option_value);
            } else {
                return $default;
            }
        }

        /**
         * @param string $option Option name without prefix.
         * @param mixed $value Any value.
         * @param string|bool|null $autoload "yes"/true/null (null also means "yes") or "no"/false.
         *                                   <i>Please notice:</i> default value of the $autoload
         *                                   here is <b>"no"</b> while the same parameter in function
         *                                   update_option() has value <b>null</b> (equal to "yes").
         * @return bool <b>true</b> if succeed, <b>false</b> - otherwise.
         */
        public function update(string $option, $value, $autoload = 'no')
        {
            $option = $this->prefix($option);
            return update_option($option, $value, $autoload);
        }

        /**
         * @param string $option Option name without prefix.
         * @return bool <b>true</b> if succeed, <b>false</b> - otherwise.
         */
        public function delete(string $option)
        {
            $option = $this->prefix($option);
            return delete_option($option);
        }
    }

}
