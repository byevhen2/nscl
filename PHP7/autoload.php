<?php

declare(strict_types = 1);

namespace just; // Change this with your namespace

/**
 * @param string|array $library Library or libraries to load.
 * @param string|array|null $_ More libraries to load.
 */
function load_module($library, $_ = null)
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
            load_module($lib);
        }
    }
}

// Notice: this autoloader here just for example, it will not work for Just
// modules. Use function load() or include required files manually.
spl_autoload_register(function ($className) {
    // "just\xxx\ClassX"
    $className = ltrim($className, '\\');

    if (strpos($className, __NAMESPACE__) !== 0) {
        return false;
    }

    // "classes\xxx\ClassX"
    $pluginFile = str_replace(__NAMESPACE__, 'classes', $className);
    // "classes/xxx/ClassX"
    $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
    // "classes/xxx/ClassX.php"
    $pluginFile .= '.php';

    require __DIR__ . DIRECTORY_SEPARATOR . $pluginFile;

    return true;
});
