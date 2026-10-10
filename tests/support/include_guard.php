<?php

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '0');

if ($argc < 2) {
    fwrite(STDERR, 'Missing target path');
    exit(1);
}

$target = $argv[1];
if (!is_file($target)) {
    fwrite(STDERR, 'Target not found');
    exit(1);
}

include $target;

echo 'SENTINEL_AFTER_INCLUDE';
