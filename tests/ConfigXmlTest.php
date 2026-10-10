<?php

class ConfigXmlTest extends ModuleTestCase
{
    public function testConfigXmlMatchesModule()
    {
        $xml = simplexml_load_file(dirname(__DIR__) . '/config.xml');
        $this->assertNotFalse($xml);
        $this->assertSame('ps_languageselector', (string) $xml->name);
        $this->assertSame('2.1.3', (string) $xml->version);
        $this->assertSame('PrestaShop', (string) $xml->author);
        $this->assertSame('0', (string) $xml->need_instance);
        $this->assertSame('0', (string) $xml->is_configurable);
        $this->assertSame('Language selector', (string) $xml->displayName);
        $description = html_entity_decode((string) $xml->description, ENT_QUOTES, 'UTF-8');
        $this->assertSame(
            "Adds a block allowing customers to select a language for your store's content.",
            $description
        );

        $module = new Ps_Languageselector();
        $this->assertSame('2.1.3', $module->version);
        $this->assertSame((string) $xml->version, $module->version);
    }
}
