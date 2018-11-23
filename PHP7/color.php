<?php

declare(strict_types = 1);

if (!function_exists('darker_color')) {
    /**
     * Make color darker by specified factor.
     *
     * @param string $hex Hex color, like "#FFF", "FFFFFF" etc.
     * @param float $factor Optional. 30 by default.
     * @return string New hex color.
     */
    function darker_color(string $hex, float $factor = 30): string
    {
        $rgb = hex_to_rgb($hex);

        $color = '#';

        foreach ($rgb as $channel) {
            $diff   = round($channel / 100 * $factor);
            $result = $channel - $diff;

            $channelHex = dechex($result);

            if (strlen($channelHex) < 2) {
                $channelHex = '0' . $channelHex;
            }

            $color .= $channelHex;
        }

        return $color;
    }
}

if (!function_exists('hex_to_rgb')) {
    /**
     * @param string $hex Hex color, like "#FFF", "FFFFFF" etc.
     * @return array ["r", "g", "b"]
     */
    function hex_to_rgb(string $hex): array
    {
        $color = str_replace('#', '', $hex);
        $color = preg_replace('/^(.)(.)(.)$/', '$1$1$2$2$3$3', $color);

        $rgb = [];

        $rgb['r'] = hexdec(substr($color, 0, 2));
        $rgb['g'] = hexdec(substr($color, 2, 2));
        $rgb['b'] = hexdec(substr($color, 4, 2));

        return $rgb;
    }
}

if (!function_exists('light_or_dark_color')) {
    /**
     * Detect if we should use a light or dark color on the background.
     *
     * @param string $hex Hex color, like "#FFF", "FFFFFF" etc.
     * @param string $dark Optional. "#000000" by default.
     * @param type $light Optional. "#FFFFFF" by default.
     * @return string
     */
    function light_or_dark_color(string $hex, string $dark = '#000000', $light = '#FFFFFF'): string
    {
        $rgb = hex_to_rgb($hex);
        $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;

        return ($brightness > 155) ? $dark : $light;
    }
}

if (!function_exists('lighter_color')) {
    /**
     * Make color lighter by specified factor.
     *
     * @param string $hex Hex color, like "#FFF", "FFFFFF" etc.
     * @param float $factor Optional. 30 by default.
     * @return string New hex color.
     */
    function lighter_color(string $hex, float $factor = 30): string
    {
        $rgb = hex_to_rgb($hex);

        $color = '#';

        foreach ($rgb as $channel) {
            $diff   = round((255 - $channel) / 100 * $factor);
            $result = $channel + $diff;

            $channelHex = dechex($result);

            if (strlen($channelHex) < 2) {
                $channelHex = '0' . $channelHex;
            }

            $color .= $channelHex;
        }

        return $color;
    }
}

if (!function_exists('to_long_hex_color')) {
    /**
     * @param string $hex Hex color, like "#FFF" or "FFF".
     * @return string Long variant of the color value, like "#FFFFFF".
     */
    function to_long_hex_color(string $hex): string
    {
        $color = str_replace('#', '', $hex);
        $color = preg_replace('/^(.)(.)(.)$/', '$1$1$2$2$3$3', $color);

        return '#' . $color;
    }
}
