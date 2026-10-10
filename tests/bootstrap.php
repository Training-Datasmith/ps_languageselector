<?php
/**
 * PHPUnit bootstrap for ps_languageselector (PHP 5.6 floor).
 */

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
date_default_timezone_set('UTC');

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '1.7.8.11');
}

$repoRoot = dirname(__DIR__);

require_once $repoRoot . '/tests/support/WidgetInterface.php';
require_once $repoRoot . '/tests/support/InstallEventLog.php';
require_once $repoRoot . '/tests/support/TranslatorRecorder.php';
require_once $repoRoot . '/tests/support/TestSmarty.php';
require_once $repoRoot . '/tests/support/Context.php';
require_once $repoRoot . '/tests/support/Language.php';
require_once $repoRoot . '/tests/support/Module.php';
require_once $repoRoot . '/tests/support/LinkStub.php';
require_once $repoRoot . '/tests/support/FixtureReset.php';
require_once $repoRoot . '/tests/support/GuardProcess.php';
require_once $repoRoot . '/tests/support/ModuleTestCase.php';

require_once $repoRoot . '/ps_languageselector.php';
