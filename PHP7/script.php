<?php

/*
 * Functions:
 *     get_args
 *     php_executable
 *     run
 *     run_in_background
 */

declare(strict_types = 1);

if (!function_exists('get_args')) {
    /**
     * @param array|null $merge Values to merge.
     * @param bool $skipFirst Skip the called script name from the arguments list.
     * @return array
     */
    function get_args($merge = null, bool $skipFirst = false): array
    {
        $args = (!$skipFirst) ? $argv : array_slice($argv, 1);

        if (!empty($merge)) {
            $args = array_merge($args, $merge);
        }

        return $args;
    }
}

if (!function_exists('php_executable')) {
    function php_executable(string $scriptFile, array $args = []): string
    {
        // Get path to PHP binary
        exec('find ' . PHP_BINDIR . ' -name php', $searchResults);
        $php = ($searchResults[0] ?? PHP_BINARY);

        // Escape arguments
        $args = array_map(function ($argument) {
            return escapeshellarg($argument);
        }, $args);

        $command = "{$php} {$scriptFile} " . implode(' ', $args);

        return escapeshellcmd($command);
    }
}

if (!function_exists('run')) {
    /**
     * @param string $scriptFile
     * @param array $args
     * @param array $output If the <b>$output</b> argument is present, then the
     *                      specified array will be filled with every line of output
     *                      from the command.
     * @return string The last line from the result of the command.
     */
    function run(string $scriptFile, array $args = [], array &$output = null): string
    {
        $command = php_executable($scriptFile, $args);
        return exec($command, $output);
    }
}

if (!function_exists('run_in_background')) {
    /**
     * @param string $scriptFile
     * @param array $args
     *
     * @author Arno van den Brink
     * @link http://php.net/manual/en/function.exec.php#86329
     */
    function run_in_background(string $scriptFile, array $args = [])
    {
        $command = php_executable($scriptFile, $args);

        if (substr(php_uname(), 0, 7) == 'Windows') {
            pclose(popen("start /B {$command}", 'r'));
        } else {
            exec("{$command} > /dev/null &");
        }
    }
}
