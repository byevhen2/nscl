<?php

declare(strict_types = 1);

/**
 * The function only checks the actions that was added by itself
 * (does not check what you previously added by add_action()).
 *
 * @param string $hook
 * @param callable $callback
 * @param int $priority Optional. 10 by default.
 * @param int $argc Optional. The number of accepted arguments. 1 by default.
 * @return bool Whether the action was added or not.
 */
function add_action_once(string $hook, callable $callback, int $priority = 10, int $argc = 1): bool
{
    return add_filter_once($hook, $callback, $priority, $argc);
}

/**
 * The function only checks the filters that was added by itself
 * (does not check what you previously added by add_filter()).
 *
 * @param string $hook
 * @param callable $callback
 * @param int $priority Optional. 10 by default.
 * @param int $argc Optional. The number of accepted arguments. 1 by default.
 * @return bool Whether the filter was added or not.
 *
 * @global string[] $wp_filter_classes
 */
function add_filter_once(string $hook, callable $callback, int $priority = 10, int $argc = 1): bool
{
    global $wp_filter_classes;

    if (!isset($wp_filter_classes)) {
        $wp_filter_classes = [];
    }

    // Since references to different instances produces a unique ID,
    // just use the class for identification purposes
    $callbackId = unique_filter_class_id($hook, $callback, $priority);

    if (!in_array($callbackId, $wp_filter_classes)) {
        $wp_filter_classes[] = $callbackId;

        // add_filter() always returns TRUE
        return add_filter($hook, $callback, $priority, $argc);
    } else {
        return false;
    }
}

/**
 * @param string $hook
 * @param callable $callback
 * @param int $priority Optional. 10 by default.
 * @param int $argc Optional. The number of accepted arguments. 1 by default.
 * @return true Always returns TRUE.
 */
function add_one_time_action(string $hook, callable $callback, int $priority = 10, int $argc = 1): bool
{
    return add_one_time_filter($hook, $callback, $priority, $argc);
}

/**
 * @param string $hook
 * @param callable $callback
 * @param int $priority Optional. 10 by default.
 * @param int $argc Optional. The number of accepted arguments. 1 by default.
 * @return true Always returns TRUE.
 */
function add_one_time_filter(string $hook, callable $callback, int $priority = 10, int $argc = 1): bool
{
    $caller = null;
    $caller = function () use ($hook, $callback, $priority, &$caller) {
        $args = func_get_args();
        $response = call_user_func_array($callback, $args);

        remove_filter($hook, $caller, $priority);

        return $response;
    };

    // add_filter() always return TRUE
    return add_filter($hook, $caller, $priority, $argc);
}

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
 * @param int $time
 * @return int
 */
function convert_to_wp_time(int $time): int
{
    // Do just like current_time() does with offset
    return $time + (int)(get_option('gmt_offset') * HOUR_IN_SECONDS);
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
 * @param int $month
 * @param int $year
 * @return int
 */
function days_in_month(int $month, int $year): int
{
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    return apply_filters('days_in_month', $daysInMonth, $month, $year);
}

function get_current_frontend_url(bool $stripQueryArgs = false): string
{
    $url = get_permalink();

    if (!empty($_SERVER['QUERY_STRING']) && !$stripQueryArgs) {
        $url .= '?' . $_SERVER['QUERY_STRING'];
    }

    return $url;
}

function get_editing_post_id(): int
{
    $postId = 0;

    if (isset($_REQUEST['post_ID']) && is_numeric($_REQUEST['post_ID'])) {
        $postId = intval($_REQUEST['post_ID']); // On post update ($_POST)

    } else if (isset($_REQUEST['post']) && is_numeric($_REQUEST['post'])) {
        $postId = intval($_REQUEST['post']); // On post edit page ($_GET)
    }

    return $postId;
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

/**
 * @return string "Region/City" or "UTC+2".
 */
function get_wp_timezone(): string
{
    $timezone = get_option('timezone_string', '');

    if (empty($timezone)) {
        $gmtOffset = (float)get_option('gmt_offset', 0);
        $timezone  = gmt2utc($gmtOffset);
    }

    return $timezone;
}

function is_active_plugin(string $plugin): bool
{
    if (!function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active($plugin);
}

/**
 * Determine if the current view is the "All" view.
 *
 * @see \WP_Posts_List_Table::is_base_request()
 *
 * @param string|null $postType Optional. NULL by default.
 * @return bool
 *
 * @global string $typenow
 */
function is_base_request(string? $postType = null): bool
{
    global $typenow;

    $unallowedArgs = [
        'filter_action' => true, // "Filter" button clicked
        'author'        => true, // Filter by post author
        'm'             => true, // Filter by date
        's'             => true  // Custom search
    ];

    $unallowedVars = array_intersect_key($_GET, $unallowedArgs);

    $isBase = count($unallowedVars) == 0;

    // Filter by post status
    if (isset($_GET['post_status']) && $_GET['post_status'] !== 'all') {
        $isBase = false;
    }

    // It's not a base request anymore when requesting posts for all languages
    if (isset($_GET['lang']) && $_GET['lang'] === 'all') {
        $isBase = false;
    }

    // Add additional check of the post type
    if (!is_null($postType) && $isBase) {
        $isBase = $postType === $typenow;
    }

    return $isBase;
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

/**
 * @param string $string Any string to generate slug of.
 * @param string $fallbackSlug A slug to use if a $string or its slug is empty.
 * @return string A string with lowercased words and dashes "-" (without
 *     underscores "_").
 */
function make_dash_slug(string $string, string $fallbackSlug = ''): string
{
    $slug = make_slug($string, '');

    if ($slug !== '') {
        // Replace all underscores. Also fix "a-_b" to "a-b"
        $slug = preg_replace('/[\-_]+/', '-', $slug);
        $slug = trim($slug, '-');
    }

    return $slug ?: $fallbackSlug;
}

/**
 * Alias of sanitize_title().
 *
 * @param string $string Any string to generate slug of.
 * @param string $fallbackSlug A slug to use if a $string or its slug is empty.
 * @return string A string with lowercased words, underscores "_" and dashes "-".
 */
function make_slug(string $string, string $fallbackSlug = ''): string
{
    // Decode any %## encoding in the title
    $slug = urldecode($string);

    // Generate slug
    $slug = sanitize_title($slug, $fallbackSlug);

    // Decode any %## encoding again after function sanitize_title(), to
    // translate something like "%d0%be%d0%b4%d0%b8%d0%bd" into "один"
    $slug = urldecode($slug);

    return $slug;
}

/**
 * @param string $slug
 * @return string
 */
function make_title(string $slug): string
{
    return ucfirst(str_replace(['-', '_'], ' ', $slug));
}

function mime_type(string $path): string
{
    $mime = wp_check_filetype(basename($path));
    return ($mime['type'] ?: 'undefined/none');
}

/**
 * @return string[] [Month number (starting from 1) => Month name]
 */
function month_names(): array
{
    $gregorianCalendar = cal_info(CAL_GREGORIAN);
    $months = array_map('translate', $gregorianCalendar['months']);

    return apply_filters('month_names', $months);
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

/**
 * @param \DateTime $date
 */
function set_wp_timezone(\DateTime $date)
{
    $date->setTimezone(wp_timezone());
}

/**
 * Produces the same filter ID for all instances of the same class.
 *
 * @param string $hook
 * @param callable $callback
 * @param int $priority
 * @return string
 */
function unique_filter_class_id($hook, $callback, $priority)
{
    if (is_array($callback) && is_object($callback[0])) {
        // Use the class name to generate the ID
        $callback[0] = get_class($callback[0]);
    }

    return _wp_filter_build_unique_id($hook, $callback, $priority);
}

function verify_nonce(string $action, string $nonceName = 'nonce'): bool
{
    if (!isset($_REQUEST[$nonceName])) {
        return false;
    }

    $nonce = $_REQUEST[$nonceName];

    return wp_verify_nonce($nonce, $action);
}

function wp_empty($value): bool
{
    return (empty($value) || is_wp_error($value));
}

/**
 * Shortcut for current_time().
 *
 * @return int Current time with timezone offset.
 */
function wp_time(): int
{
    return current_time('timestamp', true);
}
