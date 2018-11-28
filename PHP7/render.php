<?php

declare(strict_types = 1);

if (!function_exists('render_output')) {
    /**
     * @param callable $callback
     * @param mixed $atts Optional. Single parameter or an array of parameters.
     *                    Null by default (no parameters).
     * @return string
     */
    function render_output(callable $callback, $atts = null): string
    {
        ob_start();

        if (empty($atts)) {
            $result = call_user_func($callback);
        } else if (!is_array($atts)) {
            $result = call_user_func($callback, $atts);
        } else {
            $result = call_user_func_array($callback, $atts);
        }

        $output = ob_get_clean();

        if (!empty($output)) {
            return $output;
        } else if (is_string($result)) {
            return $result;
        } else {
            return '';
        }
    }
} 
