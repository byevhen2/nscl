<?php

/*
 * Functions:
 *     file_dir
 *     file_extension
 *     file_name
 *     full_file_name
 *     unique_filename
 */

declare(strict_types = 1);

if (!function_exists('file_dir')) {
    /**
     * @param string $path Path to file
     * @return string Absolute path to parent directory (without trailing slash).
     *                For the file <i>"/www/docs/inc/lib.inc.php"</i> it will be
     *                <i>"/www/docs/inc"</i>.
     */
    function file_dir(string $path): string
    {
        $fileInfo = pathinfo($path);
        return ($fileInfo['dirname'] ?? '');
    }
}

if (!function_exists('file_extension')) {
    /**
     * @param string $path Path to file
     * @return string File extension. For the file <i>"/www/docs/inc/lib.inc.php"</i>
     *                it will be <i>"php"</i>.
     */
    function file_extension(string $path): string
    {
        $fileInfo = pathinfo($path);
        return ($fileInfo['extension'] ?? '');
    }
}

if (!function_exists('file_name')) {
    /**
     * @param string $path Path to file
     * @return string For the file <i>"/www/docs/inc/lib.inc.php"</i> it will be
     *                <i>"lib.inc"</i>.
     */
    function file_name(string $path): string
    {
        $fileInfo = pathinfo($path);
        return ($fileInfo['filename'] ?? '');
    }
}

if (!function_exists('full_file_name')) {
    /**
     * @param string $path Path to file
     * @return string Full file name. For the file <i>"/www/docs/inc/lib.inc.php"</i>
     *                it will be <i>"lib.inc.php"</i>.
     */
    function full_file_name(string $path): string
    {
        $fileInfo = pathinfo($path);
        return ($fileInfo['basename'] ?? '');
    }
}

if (!function_exists('unique_filename')) {
    function unique_filename(string $filename, string $dir): string
    {
        $dir = rtrim($dir, '\/') . '/';

        if (!file_exists($dir . $filename)) {
            return $filename;
        }

        $name = file_name($filename);
        $ext  = file_extension($filename);

        for ($suffix = 2, $iterationsLimit = 1000; $suffix <= $iterationsLimit; $suffix++) {
            $filename = "{$name}-{$suffix}.{$ext}";
            if (!file_exists($dir . $filename)) {
                break;
            }
        }

        return $filename;
    }
}
