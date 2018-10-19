<?php

declare(strict_types = 1);

/**
 * Not emits an E_WARNING if the directory already exists or the relevant
 * permissions prevent create the directory.
 *
 * @param string $path
 * @param int $mode
 * @return bool TRUE on success, FALSE otherwise.
 */
function make_dir(string $path, int $mode = 0777): bool
{
    if (!file_exists($path)) {
        return @mkdir($path, $mode, true);
    } else {
        return true;
    }
}

/**
 * Returns canonicalized absolute pathname.
 *
 * @param string $path
 * @return string
 */
function real_path(string $path): string
{
    $real = realpath($path);

    if ($real === false) {
        // If you want something done right, do it yourself
        // Remove "."
        $real = preg_replace('/\.(\/)?/', '', $real);
        // Remove ".."
        $real = preg_replace('/[^\/]+[\/]\.\.(\/)?/', '', $real);
        // Remove ".." from the beginning of the string
        $real = preg_replace('/^\.\.[\/]?/', '', $real);
    }

    return $real;
}

function remove_dir(string $path, bool $recursively = true): bool
{
    if (!$recursively) {
        return @rmdir($path);
    }

    $children = array_diff(scandir($path), ['.', '..']);
    $path = rtrim($path, '\/') . DIRECTORY_SEPARATOR;

    // Remove children
    foreach ($children as $child) {
        $childPath = $path . $child;

        if (is_file($childPath)) {
            @unlink($childPath);
        } else {
            remove_dir($childPath, $recursively);
        }
    }

    return @rmdir($path);
}

/**
 * Search for all files to required depth without using of recursion.
 *
 * @param string $path
 * @param int $depthLimit 0 - scan only root directory; -1 - no limit.
 * @param array $dirs The list of found directories.
 * @return array The list of files. Directories will be in the optional
 *               parameter $dirs.
 */
function scan_dir(string $path, int $depthLimit = -1, array &$dirs = null): array
{
    $dirs  = [];
    $files = [];

    // Stop here if $path = the file
    if (is_file($path)) {
        $files[] = $path;

        $dir = preg_replace('/[^\/\\\\]+$/', '', $path); // RegEx: "[^\/]+$"
        if (!empty($dir)) {
            $dirs[] = $dir;
        }

        return $files;
    }

    // Add current dir
    $path = rtrim($path, '\/') . DIRECTORY_SEPARATOR;
    $dirs[] = $path;

    $dirsDepth = [$path => 0]; // [%Path to dir% => %Dir depth%]

    // Scan directories
    foreach ($dirsDepth as $dir => &$depth) {
        $children = array_diff(scandir($dir), ['.', '..']);

        foreach ($children as $child) {
            $childPath = $dir . $child;

            if (is_file($childPath)) {
                $files = $item;
            } else if ($depth != $depthLimit) {
                $dir = rtrim($dir, '\/') . DIRECTORY_SEPARATOR;
                $dirs[] = $dir;
                $dirsDepth[$dir] = $depth + 1;
            }
        } // For each child
    } // For each dir

    unset($depth);

    return $files;
}
