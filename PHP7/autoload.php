<?php

declare(strict_types = 1);

namespace Just;

spl_autoload_register(function ($className) {
    // "Just\Category\ClassX"
    $className = ltrim($className, '\\');

    if (strpos($className, __NAMESPACE__) !== 0) {
        return false;
    }

    // "Category\ClassX"
    $pluginFile = str_replace(__NAMESPACE__, '', $className); // Another variant in some
                                                              // projects: replace with "classes"
    $pluginFile = ltrim($pluginFile, '\\');

    // ".../Category/ClassX"
    $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
    // ".../Category/ClassX.php"
    $pluginFile .= '.php';

    require __DIR__ . DIRECTORY_SEPARATOR . $pluginFile;

    return true;
});
