<?php

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '0');

if ($argc < 2) {
    fwrite(STDERR, 'Missing script path');
    exit(1);
}

$script = $argv[1];
$args = array_slice($argv, 2);

$GLOBALS['argv'] = array_merge(array($script), $args);
$GLOBALS['argc'] = count($GLOBALS['argv']);

include $script;
