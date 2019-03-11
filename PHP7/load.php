<?php

declare(strict_types = 1);

if (!function_exists('nscl_load')) {
    /**
     * @param string|array $library Library or libraries to load.
     * @param string|array|null $_ More libraries to load.
     */
    function nscl_load($library, $_ = null)
    {
        if (is_null($_)) {
            $dir = __DIR__;

            if (is_array($library)) {
                foreach ($library as $lib) {
                    require_once "{$dir}/{$lib}.php";
                }
            } else {
                require_once "{$dir}/{$library}.php";
            }

        } else {
            $libraries = func_get_args();

            foreach ($libraries as $lib) {
                nscl_load($lib);
            }
        }
    }
}
