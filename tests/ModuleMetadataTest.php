<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class ModuleMetadataTest extends ModuleTestCase
{
    public function testConstructorIdentity()
    {
        $module = new Ps_Languageselector();
        $this->assertSame('ps_languageselector', $module->name);
        $this->assertSame('front_office_features', $module->tab);
        $this->assertSame('PrestaShop', $module->author);
        $this->assertSame('2.1.3', $module->version);
        $this->assertSame(0, $module->need_instance);
    }

    public function testConstructorTranslationStringsAndDomain()
    {
        $module = new Ps_Languageselector();
        $this->assertSame('Language selector block', $module->displayName);
        $this->assertSame(
            'Go international and display a language selector in the header of your store so that customers can shop in their own language.',
            $module->description
        );
        $this->assertCount(2, TranslatorRecorder::$calls);
        foreach (TranslatorRecorder::$calls as $call) {
            $this->assertSame('Modules.Languageselector.Admin', $call['domain']);
            $this->assertSame(array(), $call['parameters']);
            $this->assertNull($call['locale']);
        }
    }

    public function testVersionCompliancy()
    {
        $module = new Ps_Languageselector();
        $this->assertSame('1.7.1.0', $module->ps_versions_compliancy['min']);
        $this->assertSame('1.7.8.11', $module->ps_versions_compliancy['max']);
        $this->assertSame('1.7.8.11', _PS_VERSION_);
    }

    public function testImplementsWidgetInterfaceAndLegacyConstant()
    {
        $module = new Ps_Languageselector();
        $this->assertInstanceOf('PrestaShop\\PrestaShop\\Core\\Module\\WidgetInterface', $module);
        $this->assertSame('blocklanguages', Ps_Languageselector::PS_16_EQUIVALENT_MODULE);

        $reflection = new ReflectionClass('Ps_Languageselector');
        foreach (array('renderWidget', 'getWidgetVariables') as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic());
            $params = $method->getParameters();
            $this->assertCount(2, $params);
            $this->assertSame('configuration', $params[1]->getName());
            $this->assertTrue($params[1]->isArray());
        }
    }

    public function testTemplateProperty()
    {
        $reflection = new ReflectionClass('Ps_Languageselector');
        $property = $reflection->getProperty('templateFile');
        $property->setAccessible(true);
        $module = new Ps_Languageselector();
        $this->assertSame('module:ps_languageselector/ps_languageselector.tpl', $property->getValue($module));
    }
}
