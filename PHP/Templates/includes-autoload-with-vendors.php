<?php

namespace NSCL;

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    if (strpos($class, __NAMESPACE__) !== 0) {
        return; // Not ours
    }

    $vendors = [
    ];

    if (array_key_exists($class, $vendors)) {
        $file = $vendors[$class];
    } else {
        // Split into namespace and class name
        if (!preg_match('/(.+\\)(.+)/', $class, $components)) {
            return; // Failed
        }

        $namespace = $components[1]; // Something like "Namespace\Package\SubPackage\"
        $className = $components[2]; // Something like "ClassX"

        // "Namespace\Package\SubPackage\" -> "includes/package/sub-package/"
        $namespace = str_replace(__NAMESPACE__, 'includes', $namespace);
        $namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $namespace = preg_replace('/([a-z])([A-Z])/', '$1-$2', $namespace);
        $namespace = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $namespace);
        $namespace = strtolower($namespace);

        // "includes/package/sub-package/ClassX.php", leave class name without changes
        $file = $namespace . $className . '.php';
    }

    require PLUGIN_DIR . $file;
});
