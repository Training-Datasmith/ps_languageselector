<?php

class InstallTest extends ModuleTestCase
{
    protected function setUp()
    {
        parent::setUp();
        InstallEventLog::$parentInstallReturn = true;
    }

    private function legacyModule()
    {
        $legacy = new Module();
        $legacy->name = 'blocklanguages';

        return $legacy;
    }

    public function testInstallWithoutLegacyReturnsTrue()
    {
        Module::$strictGetInstance = true;
        $module = new Ps_Languageselector();
        $this->assertFalse(Module::isInstalled('blocklanguages'));
        $this->assertTrue($module->install());
        $this->assertSame(array('parent'), InstallEventLog::$events);
    }

    public function testInstallWithoutLegacyPropagatesFalse()
    {
        Module::$strictGetInstance = true;
        InstallEventLog::$parentInstallReturn = false;
        $module = new Ps_Languageselector();
        $result = $module->install();
        $this->assertInternalType('bool', $result);
        $this->assertSame(false, $result);
    }

    public function testInstallUninstallsLegacyBeforeParent()
    {
        $legacy = $this->legacyModule();
        Module::registerInstance('blocklanguages', $legacy);
        $module = new Ps_Languageselector();
        $this->assertTrue(Module::isInstalled('blocklanguages'));
        $this->assertTrue($module->install());
        $this->assertFalse(Module::isInstalled('blocklanguages'));
        $this->assertSame(array('uninstall', 'parent'), InstallEventLog::$events);
    }

    public function testInstallWhenLegacyInstanceMissing()
    {
        Module::setInstalled('blocklanguages', true);
        $module = new Ps_Languageselector();
        $this->assertTrue($module->install());
        $this->assertTrue(Module::isInstalled('blocklanguages'));
        $this->assertSame(array('parent'), InstallEventLog::$events);
    }

    public function testInstallPropagatesParentFalseAfterUninstall()
    {
        $legacy = $this->legacyModule();
        Module::registerInstance('blocklanguages', $legacy);
        InstallEventLog::$parentInstallReturn = false;
        $module = new Ps_Languageselector();
        $this->assertFalse($module->install());
        $this->assertFalse(Module::isInstalled('blocklanguages'));
    }
}
