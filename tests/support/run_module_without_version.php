<?php

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '0');

$moduleFile = dirname(dirname(__DIR__)) . '/ps_languageselector.php';
include $moduleFile;

echo 'SENTINEL_AFTER_INCLUDE';
