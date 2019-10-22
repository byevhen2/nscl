<?php

namespace NSCL;

spl_autoload_register(function ($className) {
    // "Namespace\Package\ClassX"
    $className = ltrim($className, '\\');

    if (strpos($className, __NAMESPACE__) !== 0) {
        return;
    }

    // "classes\Package\ClassX"
    $pluginFile = str_replace(__NAMESPACE__, 'classes', $className);
    // "classes/Package/ClassX"
    $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
    // "classes/Package/Class-X"
    $pluginFile = preg_replace('/([a-z])([A-Z])/', '$1-$2', $pluginFile);
    $pluginFile = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $pluginFile);
    // "classes/package/class-x"
    $pluginFile = strtolower($pluginFile);
    // "classes/package/class-x.php"
    $pluginFile .= '.php';
    // ".../project-dir/classes/package/class-x.php"
    $pluginFile = __DIR__ . DIRECTORY_SEPARATOR . $pluginFile;

    require $pluginFile;
});
