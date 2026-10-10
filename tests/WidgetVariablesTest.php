<?php

class WidgetVariablesTest extends ModuleTestCase
{
    private function seedDefaultLanguages()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English (English)',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
            ),
            array(
                'id_lang' => 2,
                'id_shop' => 1,
                'name' => 'Français (French)',
                'iso_code' => 'fr',
                'language_code' => 'fr-fr',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $context->language->id = 2;
        $context->language->name = 'Français (French)';
        $context->language->iso_code = 'fr';
    }

    public function testStripsTrailingParenthetical()
    {
        $this->seedDefaultLanguages();
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertInternalType('string', $vars['languages'][0]['name_simple']);
        $this->assertSame('English', $vars['languages'][0]['name_simple']);
        $this->assertInternalType('string', $vars['current_language']['name_simple']);
        $this->assertSame('Français', $vars['current_language']['name_simple']);
    }

    public function testPlainNameUnchanged()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'Deutsch',
                'iso_code' => 'de',
                'language_code' => 'de-de',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $context->language->name = 'Deutsch';
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertInternalType('string', $vars['languages'][0]['name_simple']);
        $this->assertSame('Deutsch', $vars['languages'][0]['name_simple']);
    }

    public function testParenthesisWithoutSpaceUnchanged()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English(English)',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $context->language->name = 'English(English)';
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertSame('English(English)', $vars['languages'][0]['name_simple']);
    }

    public function testNonTerminalParentheticalUnchanged()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English (UK) extra',
                'iso_code' => 'en',
                'language_code' => 'en-gb',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $context->language->name = 'English (UK) extra';
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertSame('English (UK) extra', $vars['languages'][0]['name_simple']);
    }

    public function testEmptyName()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => '',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $context->language->name = '';
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertInternalType('string', $vars['languages'][0]['name_simple']);
        $this->assertSame('', $vars['languages'][0]['name_simple']);
    }

    public function testRowKeysAndOrderPreserved()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 2,
                'id_shop' => 1,
                'name' => 'Français (French)',
                'iso_code' => 'fr',
                'language_code' => 'fr-fr',
                'active' => 1,
            ),
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English (English)',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertCount(2, $vars['languages']);
        $this->assertSame(2, $vars['languages'][0]['id_lang']);
        $this->assertSame('fr', $vars['languages'][0]['iso_code']);
        $this->assertSame('fr-fr', $vars['languages'][0]['language_code']);
        $this->assertArrayHasKey('name_simple', $vars['languages'][0]);
        $this->assertSame(1, $vars['languages'][1]['id_lang']);
    }

    public function testActiveLanguagesForCurrentShopOnly()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English (English)',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
            ),
            array(
                'id_lang' => 2,
                'id_shop' => 1,
                'name' => 'Français (French)',
                'iso_code' => 'fr',
                'language_code' => 'fr-fr',
                'active' => 1,
            ),
            array(
                'id_lang' => 3,
                'id_shop' => 1,
                'name' => 'Deutsch (German)',
                'iso_code' => 'de',
                'language_code' => 'de-de',
                'active' => 0,
            ),
            array(
                'id_lang' => 4,
                'id_shop' => 2,
                'name' => 'Italiano (Italian)',
                'iso_code' => 'it',
                'language_code' => 'it-it',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $names = array();
        foreach ($vars['languages'] as $lang) {
            $names[] = $lang['name'];
        }
        $this->assertSame(array('English (English)', 'Français (French)'), $names);
    }

    public function testCurrentLanguageShape()
    {
        $this->seedDefaultLanguages();
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $current = $vars['current_language'];
        $this->assertArrayHasKey('id_lang', $current);
        $this->assertArrayHasKey('name', $current);
        $this->assertArrayHasKey('name_simple', $current);
        $this->assertArrayHasKey('iso_code', $current);
        $this->assertSame(2, $current['id_lang']);
        $this->assertSame('fr', $current['iso_code']);
    }

    public function testHookAndConfigurationDoNotCorruptVariables()
    {
        $this->seedDefaultLanguages();
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables('displayNav2', array('foo' => 'bar'));
        $this->assertCount(2, $vars['languages']);
        $this->assertSame('English (English)', $vars['languages'][0]['name']);
    }

    public function testSingleLanguageStillReturnedByGetWidgetVariables()
    {
        Language::resetCatalog(array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English (English)',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $module = new Ps_Languageselector();
        $vars = $module->getWidgetVariables();
        $this->assertCount(1, $vars['languages']);
        $this->assertInternalType('array', $vars['languages']);
    }
}
