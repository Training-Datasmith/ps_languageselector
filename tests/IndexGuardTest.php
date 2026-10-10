<?php

class IndexGuardTest extends ModuleTestCase
{
    private function repoPath($relative)
    {
        return dirname(__DIR__) . '/' . $relative;
    }

    private function assertCleanChildRun($result)
    {
        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['stdout']);
        $this->assertSame('', $result['stderr']);
    }

    public function testRootIndexExitsBeforeSentinel()
    {
        $guard = dirname(__FILE__) . '/support/include_guard.php';
        $result = GuardProcess::runPhpScript($guard, array($this->repoPath('index.php')));
        $this->assertCleanChildRun($result);
    }

    public function testTestsIndexExitsBeforeSentinel()
    {
        $guard = dirname(__FILE__) . '/support/include_guard.php';
        $result = GuardProcess::runPhpScript($guard, array($this->repoPath('tests/index.php')));
        $this->assertCleanChildRun($result);
    }

    public function testPhpstanIndexExitsBeforeSentinel()
    {
        $guard = dirname(__FILE__) . '/support/include_guard.php';
        $result = GuardProcess::runPhpScript($guard, array($this->repoPath('tests/phpstan/index.php')));
        $this->assertCleanChildRun($result);
    }

    public function testTranslationsIndexExitsBeforeSentinel()
    {
        $guard = dirname(__FILE__) . '/support/include_guard.php';
        $result = GuardProcess::runPhpScript($guard, array($this->repoPath('translations/index.php')));
        $this->assertCleanChildRun($result);
    }

    public function testModuleExitsWhenVersionConstantMissing()
    {
        $script = dirname(__FILE__) . '/support/run_module_without_version.php';
        $result = GuardProcess::runPhpScript($script);
        $this->assertCleanChildRun($result);
    }
}
