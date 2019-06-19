<?php

declare(strict_types = 1);
namespace sid;

require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/FailuresRecorder.php';

$CONFIG = [
    'base-dir'         => __DIR__ . '/downloads/',
    'start-from'       => 1,
    'count'            => 2000,
    'output-extension' => '', // jpg|gif|png|mp3|mp4|webm etc.
    'logs-file'        => 'Logs.txt' // See the log file in %base-dir%. Leave empty to disable file logs
];

// No changes required after this line
$baseDir   = rtrim($CONFIG['base-dir'], '\/') . DIRECTORY_SEPARATOR;
$startFrom = $CONFIG['start-from'];
$count     = $CONFIG['count'];
$stopAfter = $startFrom + $count - 1;
$ext       = !empty($CONFIG['output-extension']) ? '.' . $CONFIG['output-extension'] : '';

global $enableLogs, $logFile;

$enableLogs = !empty($CONFIG['logs-file']);
$logFile    = $baseDir . $CONFIG['logs-file'];

// Check base dir
if (!file_exists($baseDir)) {
    say('Error. The destination directory does not exist.');
    exit(1);
}

// Check the log file
if ($enableLogs) {
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '');

        if (!file_exists($logFile)) {
            say('Error. Failed to create a log file ' . $logFile);
            exit(2);
        }
    }
}

// All ready to start
$fails = new FailuresRecorder($startFrom);

for ($i = $startFrom, $done = 0; $i <= $stopAfter; $i++, $done++) {
    // Notify about the progress
    if ($done % 50 == 0) { // ... after each 50 items
        say("{$done}/{$count} done...");
    }

    $file = "{$baseDir}{$i}{$ext}";

    if (file_exists($file)) {
        $fails->nextSucceed();
    } else {
        $fails->nextFailed();
    }
}

$fails->finish();

say('Finished.');
say("Failed to download the following files: {$fails}.");

exit(0);
