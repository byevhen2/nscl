<?php

namespace NSCL;

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    // "Namespace\Package\ClassX"
    $class = ltrim($class, '\\');

    if (strpos($class, __NAMESPACE__) !== 0) {
        return; // Not ours
    }

    // "classes\Package\ClassX"
    $file = str_replace(__NAMESPACE__, 'classes', $class);
    // "classes/Package/ClassX"
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
    // "classes/Package/ClassX.php"
    $file .= '.php';
    // ".../classes/Package/ClassX.php"
    $file = __DIR__ . DIRECTORY_SEPARATOR . $file;

    require $file;
});
