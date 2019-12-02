<?php

namespace NSCL;

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    // "Namespace\Package\SubPackage\ClassX"
    $class = ltrim($class, '\\');

    $vendors = [
        // Class name => Custom relative path
    ];

    if (strpos($class, __NAMESPACE__) !== 0) {
        // "classes\Package\SubPackage\ClassX"
        $file = str_replace(__NAMESPACE__, 'classes', $class);
        // "classes/Package/SubPackage/ClassX"
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
        // "classes/Package/Sub-Package/Class-X"
        $file = preg_replace('/([a-z])([A-Z])/', '$1-$2', $file);
        $file = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $file);
        // "classes/package/sub-package/class-x"
        $file = strtolower($file);
        // "classes/package/sub-package/class-x.php"
        $file .= '.php';
        // ".../classes/package/sub-package/class-x.php"
        $file = __DIR__ . DIRECTORY_SEPARATOR . $file;

        require $file;

    } else if (array_key_exists($class, $vendors)) {
        // ".../vendors/*/**.php"
        $file = __DIR__ . DIRECTORY_SEPARATOR . $vendors[$class];

        require $file;
    }
});
