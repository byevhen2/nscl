<?php

declare(strict_types = 1);
namespace sid;

require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/FailuresRecorder.php';

$CONFIG = [
    'base-url'           => '', // Place your URL here
    'base-dir'           => __DIR__ . '/downloads/',
    'start-from'         => 1,
    'count'              => 2000,
    'pad-length'         => 4,
    'pad-string'         => '0',
    'input-extension'    => '', // jpg|gif|png|mp3|mp4|webm etc.
    'output-extension'   => '', // jpg|gif|png|mp3|mp4|webm etc.
    'wait-after-success' => 1, // Integer, seconds
    'wait-after-failure' => 1, // Integer, seconds
    'delay'              => 0, // Integer, seconds (delay before start)
    'logs-file'          => 'Logs.txt' // See the log file in %base-dir%. Leave empty to disable file logs
];

// No changes required after this line
$baseUrl             = $CONFIG['base-url'];
$baseDir             = rtrim($CONFIG['base-dir'], '\/') . DIRECTORY_SEPARATOR;
$startFrom           = $CONFIG['start-from'];
$stopAfter           = $startFrom + $CONFIG['count'] - 1;
$padLength           = $CONFIG['pad-length'];
$padString           = $CONFIG['pad-string'];
$inputExt            = !empty($CONFIG['input-extension'])  ? '.' . $CONFIG['input-extension']  : '';
$outputExt           = !empty($CONFIG['output-extension']) ? '.' . $CONFIG['output-extension'] : '';
$successSleepSeconds = $CONFIG['wait-after-success'];
$failSleepSeconds    = $CONFIG['wait-after-failure'];
$delay               = $CONFIG['delay'];

global $enableLogs, $logFile;

$enableLogs = !empty($CONFIG['logs-file']);
$logFile    = $baseDir . $CONFIG['logs-file'];

// Create all subdirectories, or copy() will 100% fail on every file. See
// http://php.net/manual/en/function.copy.php#62807 for more details
if (!file_exists($baseDir)) {
    $madeDir = @mkdir($baseDir, 0755, true);

    if (!$madeDir) {
        say('Error. The destination directory does not exist. Failed to create it automatically.');
        exit(1);
    }
}

// Check permissions
if (!is_writable($baseDir)) {
    say('Error. The destination directory is not writable.');
    exit(2);
}

// Check the log file
if ($enableLogs) {
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '');

        if (!file_exists($logFile)) {
            say('Error. Failed to create a log file ' . $logFile);
            exit(3);
        }
    }
}

// All ready to start
$stats = ['succeed' => 0, 'failed' => 0, 'downloads-time' => 0.0, 'total-time' => 0.0];
$fails = new FailuresRecorder($startFrom);

if ($delay > 0) {
    say('Wait ' . $delay . ' seconds before start.');
    sleep($delay);
    $stats['total-time'] += $delay;
}

for ($i = $startFrom; $i <= $stopAfter; $i++) {
    $sourceFile = str_pad((string)$i, $padLength, $padString, STR_PAD_LEFT);
    $sourceFile = "{$sourceFile}{$inputExt}"; // 0044.ext
    $sourceUri  = "{$baseUrl}{$sourceFile}"; // http://domain/catalog/0044.ext

    $destFile = "{$i}{$outputExt}"; // 44.ext
    $destPath = "{$baseDir}{$destFile}"; // ./downloads/44.ext

    say("Downloading {$destFile}... ", false);

    $startTime   = microtime(true);
    $copied      = @copy($sourceUri, $destPath);
    $endTime     = microtime(true);
    $processTime = $endTime - $startTime;

    $stats['downloads-time'] += $processTime;
    $stats['total-time'] += $processTime;

    if ($copied) {
        $stats['succeed']++;
        $fails->nextSucceed();

        // Notify about results
        say('Done. (' . format_time($processTime) . ' seconds)');

        // Make a pause
        if ($successSleepSeconds > 0) {
            sleep($successSleepSeconds);
            $stats['total-time'] += $successSleepSeconds;
        }

    } else {
        $stats['failed']++;
        $fails->nextFailed();

        // Notify about results
        $errorMessage = error_get_last()['message'];
        if (is_null($errorMessage)) {
            say('Cannot reach the specified URI. (No error message from the PHP)');
        } else {
            // Remove part "copy(...): failed to open stream:" from the error message
            $errorMessage = preg_replace('/[^:]++:/', '', $errorMessage);
            // Trim new line character from the end of the string
            $errorMessage = trim($errorMessage);
            say($errorMessage);
        }

        // Make a pause
        if ($failSleepSeconds > 0) {
            sleep($failSleepSeconds);
            $stats['total-time'] += $failSleepSeconds;
        }
    } // if !$copied
} // for $i from $startFrom to $stopAfter

$fails->finish();

$downloadsTime = format_time($stats['downloads-time']);
$totalTime = format_time($stats['total-time']);

say("Finished. {$stats['succeed']} succeed, {$stats['failed']} failed. Downloads time: ~{$downloadsTime} seconds. Total time: ~{$totalTime} seconds.");

if ($fails->hasFailures()) {
    say("Failed to download the following files: {$fails}.");
}

exit(0);
