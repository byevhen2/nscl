<?php

declare(strict_types = 1);

function stderr(string $message)
{
    fputs(STDERR, $message . PHP_EOL);
}

function stdout(string $message)
{
    fputs(STDOUT, $message . PHP_EOL);
}
