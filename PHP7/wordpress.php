<?php

/*
 * See also PHP/WordPress/*.
 */

declare(strict_types = 1);

if (!function_exists('check_hash')) {
    /**
     * @param mixed $value
     * @param string $hash
     * @return bool
     */
    function check_hash($value, string $hash): bool
    {
        $correctHash = create_hash($value);
        return ($hash === $correctHash);
    }
}

if (!function_exists('check_option')) {
    function check_option(string $option, $value, $operator = '=')
    {
        global $wpdb;

        $operator = strtoupper($operator);

        if (!in_array($operator, ['=', '!=', '>', '<', '>=', '<=', 'EXISTS'])) {
            // Invalid operator
            return false;
        }

        if ($operator == 'EXISTS') {
            $where = '';
        } else {
            $value = maybe_serialize($value);
            $where = $wpdb->prepare("AND `option_value` {$operator} %s", $value);
        }

        // The code partly from function get_option(). See also get_uncached_option()
        $suppressStatus = $wpdb->suppress_errors(); // Set to suppress errors and
                                                    // save the previous value

        $query  = $wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE `option_name` = %s {$where} LIMIT 1", $option);
        $result = $wpdb->get_var($query);

        $wpdb->suppress_errors($suppressStatus);

        return (bool)$result;
    }
}

if (!function_exists('check_option_length')) {
    function check_option_length(string $option): int
    {
        global $wpdb;

        $query  = $wpdb->prepare("SELECT LENGTH(`option_value`) FROM {$wpdb->options} WHERE `option_name` = '%s'", $option);
        $length = (int)$wpdb->get_var($query); // Also convert null (no result) into 0

        return $length;
    }
}

if (!function_exists('create_hash')) {
    /**
     * @param mixed $value
     * @return string The hash as a 32-character hexadecimal number.
     */
    function create_hash($value): string
    {
        if (function_exists('wp_hash')) {
            return wp_hash($value);
        } else {
            $salt = LOGGED_IN_KEY . LOGGED_IN_SALT;
            return hash_hmac('md5', (string)$value, $salt);
        }
    }
}

if (!function_exists('create_table')) {
    /**
     * @param string $table Table name without prefix.
     * @param array $fields Example: <pre>["id" => "INT(1) NOT NULL AUTO_INCREMENT"].</pre>
     * @param string|array $primaryKey One or more fields (field names).
     * @param array $config Table configuration parameters, like CHARSET or ENGINE.
     */
    function create_table(string $table, array $fields, $primaryKey = null, array $config = []): bool
    {
        global $wpdb;

        $table  = $wpdb->prefix . $table;
        $config = array_merge(['CHARSET' => 'utf8', 'AUTO_INCREMENT' => 1], $config);

        // Init primary key
        if (!is_null($primaryKey)) {
            if (is_array($primaryKey)) {
                $primaryKey = implode(', ', $primaryKey);
            }
        } else {
            // Get first field
            $names = array_keys($fields);
            $primaryKey = reset($names);
        }

        // Stringify $fields
        array_walk($fields, function (&$value, $name) {
            $value = "{$name} {$value}";
        });

        $fields = implode(', ', $fields);

        // Stringify $config
        array_walk($config, function (&$value, $key) {
            if (empty($value)) { // Example [..., "DEFAULT" => ""]
                $value = $key;
            } else if (!is_numeric($key)) { // Skip numeric indexes
                $value = "{$key}={$value}";
            }
        });

        $config = implode(' ', $config);

        $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$fields}, PRIMARY KEY ({$primaryKey})) {$config}";

        return $wpdb->query($sql);
    }
}

if (!function_exists('generate_slug')) {
    function generate_slug(string $title): string
    {
        // Decode any %## encoding in the title
        $slug = urldecode($title);
        // Generate slug
        $slug = sanitize_title($slug);
        // Decode any %## encoding again after function sanitize_title(), to
        // translate something like "%d0%be%d0%b4%d0%b8%d0%bd" into "один"
        $slug = urldecode($slug);
        return $slug;
    }
}

if (!function_exists('get_uncached_option')) {
    function get_uncached_option(string $option, $default = false)
    {
        global $wpdb;

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
}

if (!function_exists('mime_type')) {
    function mime_type(string $path): string
    {
        $mime = wp_check_filetype(basename($path));
        return ($mime['type'] ?: 'undefined/none');
    }
}

if (!function_exists('readable_post_statuses')) {
    /**
     * @return array "publish", and maybe "private", if current user can read
     *               private posts.
     */
    function readable_post_statuses(): array
    {
        if (current_user_can('read_private_posts')) {
            return ['publish', 'private'];
        } else {
            return ['publish'];
        }
    }
}

if (!function_exists('wp_empty')) {
    function wp_empty($value): bool
    {
        return (empty($value) || is_wp_error($value));
    }
}
