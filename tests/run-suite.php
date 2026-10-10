<?php

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

if (PHP_VERSION !== '5.6.40') {
    fwrite(STDERR, 'This suite must run on PHP 5.6.40, got ' . PHP_VERSION . PHP_EOL);
    exit(1);
}

$repoRoot = dirname(__DIR__);

$expectedFiles = array(
    'tests/ConfigXmlTest.php',
    'tests/IndexGuardTest.php',
    'tests/InstallTest.php',
    'tests/ModuleMetadataTest.php',
    'tests/RenderWidgetTest.php',
    'tests/TemplateRenderTest.php',
    'tests/WidgetVariablesTest.php',
);

$discovered = glob($repoRoot . '/tests/*Test.php');
if ($discovered === false) {
    fwrite(STDERR, 'Failed to discover test files' . PHP_EOL);
    exit(1);
}

$relativeDiscovered = array();
foreach ($discovered as $path) {
    $relativeDiscovered[] = 'tests/' . basename($path);
}
sort($relativeDiscovered);

if ($relativeDiscovered !== $expectedFiles) {
    fwrite(STDERR, 'Test file list drift detected.' . PHP_EOL);
    fwrite(STDERR, 'Expected: ' . implode(', ', $expectedFiles) . PHP_EOL);
    fwrite(STDERR, 'Found:    ' . implode(', ', $relativeDiscovered) . PHP_EOL);
    exit(1);
}

$expectedTestCount = 32;
$actualTestCount = 0;
foreach ($expectedFiles as $file) {
    $contents = file_get_contents($repoRoot . '/' . $file);
    if ($contents === false) {
        fwrite(STDERR, 'Unable to read ' . $file . PHP_EOL);
        exit(1);
    }
    if (preg_match_all('/function\s+(test[A-Za-z0-9_]*)\s*\(/', $contents, $matches)) {
        $actualTestCount += count($matches[1]);
    }
}

if ($actualTestCount !== $expectedTestCount) {
    fwrite(STDERR, 'Test count drift: expected ' . $expectedTestCount . ', found ' . $actualTestCount . PHP_EOL);
    exit(1);
}

$smartyDir = getenv('SMARTY_DIR');
if (!$smartyDir || !is_dir($smartyDir)) {
    fwrite(STDERR, 'SMARTY_DIR must point to extracted Smarty 3.1.48' . PHP_EOL);
    exit(1);
}

require_once $smartyDir . '/libs/Smarty.class.php';
if (!class_exists('Smarty')) {
    fwrite(STDERR, 'Smarty class failed to load from SMARTY_DIR' . PHP_EOL);
    exit(1);
}

$phpunitPhar = getenv('PHPUNIT_PHAR');
if (!$phpunitPhar || !is_file($phpunitPhar)) {
    fwrite(STDERR, 'PHPUNIT_PHAR must point to verified phpunit-4.8.36.phar' . PHP_EOL);
    exit(1);
}

require $phpunitPhar;

$bootstrap = $repoRoot . '/tests/bootstrap.php';
require_once $bootstrap;

$diagnosticPatterns = array(
    'Notice:',
    'Warning:',
    'Deprecated:',
    'Strict Standards:',
    'Parse error:',
    'Fatal error:',
);

/**
 * @param string $output
 * @param array  $patterns
 */
function assertNoDiagnostics($output, array $patterns)
{
    foreach ($patterns as $pattern) {
        if (stripos($output, $pattern) !== false) {
            fwrite(STDERR, 'PHP diagnostic detected in output: ' . $pattern . PHP_EOL);
            exit(1);
        }
    }
}

/**
 * @param PHPUnit_Framework_TestResult $result
 * @param string                       $label
 */
function assertCleanResult($result, $label)
{
    if (!$result->wasSuccessful()) {
        fwrite(STDERR, $label . ' run was not successful' . PHP_EOL);
        exit(1);
    }
    if ($result->skippedCount() > 0) {
        fwrite(STDERR, $label . ' run had skipped tests' . PHP_EOL);
        exit(1);
    }
    if (method_exists($result, 'riskyCount') && $result->riskyCount() > 0) {
        fwrite(STDERR, $label . ' run had risky tests' . PHP_EOL);
        exit(1);
    }
}

/**
 * @param PHPUnit_TextUI_ResultPrinter $printer
 *
 * @return string
 */
function readCapturedPrinterOutput(PHPUnit_TextUI_ResultPrinter $printer)
{
    $ref = new ReflectionClass('PHPUnit_Util_Printer');
    $outProp = $ref->getProperty('out');
    $outProp->setAccessible(true);
    $out = $outProp->getValue($printer);

    if (!is_resource($out)) {
        fwrite(STDERR, 'Failed to read PHPUnit printer capture stream' . PHP_EOL);
        exit(1);
    }

    rewind($out);
    $output = stream_get_contents($out);

    if ($output === false) {
        fwrite(STDERR, 'Failed to read PHPUnit printer capture stream' . PHP_EOL);
        exit(1);
    }

    return $output;
}

/**
 * @param PHPUnit_TextUI_TestRunner    $runner
 * @param PHPUnit_Framework_TestSuite  $suite
 * @param array                        $runnerArguments
 * @param string                       $label
 * @param array                        $patterns
 *
 * @return PHPUnit_Framework_TestResult
 */
function runCapturedSuite($runner, $suite, array $runnerArguments, $label, array $patterns)
{
    $colors = !empty($runnerArguments['colors'])
        ? PHPUnit_TextUI_ResultPrinter::COLOR_AUTO
        : PHPUnit_TextUI_ResultPrinter::COLOR_NEVER;

    $printer = new PHPUnit_TextUI_ResultPrinter(
        'php://temp',
        !empty($runnerArguments['verbose']),
        $colors
    );
    $runner->setPrinter($printer);

    $result = $runner->doRun($suite, $runnerArguments);
    $output = readCapturedPrinterOutput($printer);

    assertNoDiagnostics($output, $patterns);
    fwrite(STDOUT, $output);

    assertCleanResult($result, $label);

    return $result;
}

/**
 * @param PHPUnit_Framework_TestSuite $suite
 * @param array                       $pairs
 */
function collectTestPairs(PHPUnit_Framework_TestSuite $suite, array &$pairs)
{
    foreach ($suite as $test) {
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            collectTestPairs($test, $pairs);
        } elseif ($test instanceof PHPUnit_Framework_TestCase) {
            $pairs[] = array(get_class($test), $test->getName());
        }
    }
}

$configurationFile = $repoRoot . '/phpunit.xml.dist';

$runnerArguments = array(
    'bootstrap' => $bootstrap,
    'configuration' => $configurationFile,
    'testdox' => false,
    'verbose' => true,
    'colors' => true,
    'disallowTestOutput' => true,
    'reportUselessTests' => true,
);

$orderedSuite = new PHPUnit_Framework_TestSuite('module-tests');
foreach ($expectedFiles as $file) {
    $orderedSuite->addTestFile($repoRoot . '/' . $file);
}
$pairs = array();
collectTestPairs($orderedSuite, $pairs);
if (count($pairs) !== $expectedTestCount) {
    fwrite(STDERR, 'Could not collect all test pairs before ordered run' . PHP_EOL);
    exit(1);
}

$orderedRunner = new PHPUnit_TextUI_TestRunner();
$orderedResult = runCapturedSuite($orderedRunner, $orderedSuite, $runnerArguments, 'Ordered', $diagnosticPatterns);

if ($orderedResult->count() !== $expectedTestCount) {
    fwrite(STDERR, 'Ordered run test count mismatch' . PHP_EOL);
    exit(1);
}

srand(20261010);
shuffle($pairs);

$shuffledSuite = new PHPUnit_Framework_TestSuite('module-tests-shuffled');
$addTestMethod = new ReflectionMethod('PHPUnit_Framework_TestSuite', 'addTestMethod');
$addTestMethod->setAccessible(true);
foreach ($pairs as $pair) {
    $classReflection = new ReflectionClass($pair[0]);
    $methodReflection = new ReflectionMethod($pair[0], $pair[1]);
    $addTestMethod->invoke($shuffledSuite, $classReflection, $methodReflection);
}

$shuffledRunner = new PHPUnit_TextUI_TestRunner();
$shuffledResult = runCapturedSuite($shuffledRunner, $shuffledSuite, $runnerArguments, 'Shuffled', $diagnosticPatterns);

if ($shuffledResult->count() !== $expectedTestCount) {
    fwrite(STDERR, 'Shuffled run test count mismatch' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'OK (' . ($expectedTestCount * 2) . ' tests across ordered and shuffled runs)' . PHP_EOL);
exit(0);
