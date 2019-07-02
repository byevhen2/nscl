<?php

declare(strict_types = 1);

/**
 * Add "?" sign to the URL.
 *
 * @param string $url
 * @param string $queryVar Optional. "s" by default.
 * @return string
 */
function add_query_sign(string $url, string $queryVar = 's'): string
{
    if (strpos($url, '?') === false) {
        $url = add_query_arg($queryVar, '', $url);
    }

    return $url;
}

function ajax_url(): string
{
    return admin_url('admin-ajax.php');
}

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

function check_option_length(string $option): int
{
    global $wpdb;

    $query  = $wpdb->prepare("SELECT LENGTH(`option_value`) FROM {$wpdb->options} WHERE `option_name` = '%s'", $option);
    $length = (int)$wpdb->get_var($query); // Also convert null (no result) into 0

    return $length;
}

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

/**
 * @param string $title
 * @return string
 *
 * @see generate_text()
 */
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

/**
 * @param string $slug
 * @return string
 *
 * @see generate_slug()
 */
function generate_text(string $slug): string
{
    return ucfirst(str_replace(['-', '_'], ' ', $slug));
}

function get_current_frontend_url(bool $stripQueryArgs = false): string
{
    $url = get_permalink();

    if (!empty($_SERVER['QUERY_STRING']) && !$stripQueryArgs) {
        $url .= '?' . $_SERVER['QUERY_STRING'];
    }

    return $url;
}

/**
 * @global wpdb $wpdb
 *
 * @param string $option Option name.
 * @param mixed $default Optional. <b>false</b> by default.
 * @return mixed Option value or default value.
 *
 * @see NSCL\WordPress\Options::getUncached()
 */
function get_uncached_option(string $option, $default = false)
{
    global $wpdb;

    // The code partly from function get_option()
    $suppressStatus = $wpdb->suppress_errors(); // Set to suppress errors and
                                                // save the previous value

    $query = $wpdb->prepare("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name` = %s LIMIT 1", $option);
    $row   = $wpdb->get_row($query);

    $wpdb->suppress_errors($suppressStatus);

    if (is_object($row)) {
        return maybe_unserialize($row->option_value);
    } else {
        return $default;
    }
}

function is_active_plugin(string $plugin): bool
{
    if (!function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active($plugin);
}

function is_edit_post(): bool
{
    global $pagenow;
    return is_admin() && in_array($pagenow, array('post.php', 'post-new.php'));
}

function is_edit_post_type(string $postType): bool
{
    global $pagenow, $typenow;
    return is_admin() && in_array($pagenow, array('post.php', 'post-new.php')) && $typenow === $postType;
}

function is_wp_version(string $atLeast, bool $clean = false): bool
{
    global $wp_version;

    $version = $clean ? preg_replace('/[^\d\.].*$/', '', $wp_version) : $wp_version;

    return version_compare($version, $atLeast, '>=');
}

function mime_type(string $path): string
{
    $mime = wp_check_filetype(basename($path));
    return ($mime['type'] ?: 'undefined/none');
}

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

function wp_empty($value): bool
{
    return (empty($value) || is_wp_error($value));
}
