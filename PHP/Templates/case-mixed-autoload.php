<?php

namespace NSCL;

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    // "Namespace\Package\SubPackage\ClassX"
    $class = ltrim($class, '\\');

    if (strpos($class, __NAMESPACE__) !== 0) {
        return; // Not ours
    }

    preg_match('/(?<namespace>.*\\\\)(?<class>.+)/', $class, $parts);

    // "ClassX"
    $filename = $parts['class'];
    // "ClassX.php"
    $filename .= '.php';

    // "Namespace\Package\SubPackage\"
    $dir = $parts['namespace'];
    // "classes\Package\SubPackage"
    $dir = str_replace(__NAMESPACE__, 'classes', $dir);
    // "classes/Package/SubPackage"
    $dir = str_replace('\\', DIRECTORY_SEPARATOR, $dir);
    // "classes/Package/Sub-Package/"
    $dir = preg_replace('/([a-z])([A-Z])/', '$1-$2', $dir);
    $dir = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $dir);
    // "classes/package/sub-package/"
    $dir = strtolower($dir);

    // ".../classes/package/sub-package/ClassX.php"
    $file = __DIR__ . DIRECTORY_SEPARATOR . $dir . $filename;

    require $file;
});
