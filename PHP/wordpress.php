<?php

/*
 * See also PHP/WordPress/*.
 */

declare(strict_types = 1);

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

    // The code partly from function get_option(). See also
    // get_uncached_option() here
    $suppressStatus = $wpdb->suppress_errors();

    $query  = $wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->options} WHERE `option_name` = %s {$where} LIMIT 1", $option);
    $result = $wpdb->get_var($query);

    $wpdb->suppress_errors($suppressStatus);

    return (bool)$result;
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

function get_uncached_option(string $option, $default = false)
{
    global $wpdb;

    // The code partly from function get_option()
    $suppressStatus = $wpdb->suppress_errors();

    $query = $wpdb->prepare("SELECT `option_value` FROM {$wpdb->options} WHERE `option_name` = %s LIMIT 1", $option);
    $row   = $wpdb->get_row($query);

    $wpdb->supress_errors($suppressStatus);

    if (is_object($row)) {
        return maybe_unserialize($row->option_value);
    } else {
        return $default;
    }
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
