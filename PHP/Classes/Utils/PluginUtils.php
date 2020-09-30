<?php

declare(strict_types = 1);

namespace NSCL\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @since 1.0
 */
class PluginUtils
{
    /**
     * @param string $slug
     * @return bool
     * @since 1.0
     */
    public static function isPluginActive(string $slug): bool
    {
        $activePlugins = get_option('active_plugins', []);
        return in_array($slug, $activePlugins);
    }

    /**
     * @param string $slug
     * @return bool
     * @since 1.0
     */
    public static function isPluginInstalled(string $slug): bool
    {
        return file_exists(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug);
    }
}
