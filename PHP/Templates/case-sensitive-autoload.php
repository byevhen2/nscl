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
    // "classes/Package/ClassX.php"
    $pluginFile .= '.php';
    // ".../project-dir/classes/Package/ClassX.php"
    $pluginFile = __DIR__ . DIRECTORY_SEPARATOR . $pluginFile;

    require $pluginFile;
});
