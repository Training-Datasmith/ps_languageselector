<?php

class RenderWidgetTest extends ModuleTestCase
{
    private function twoLanguageCatalog()
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

    public function testFalseForZeroLanguages()
    {
        Language::clearCatalog();
        $context = Context::getInstance();
        $context->shop->id = 1;
        Module::$forbidAssignAndFetch = true;
        $module = new Ps_Languageselector();
        $this->assertSame(false, $module->renderWidget());
    }

    public function testFalseForOneLanguage()
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
        Module::$forbidAssignAndFetch = true;
        $module = new Ps_Languageselector();
        $this->assertSame(false, $module->renderWidget());
    }

    public function testRendersForTwoLanguages()
    {
        $this->twoLanguageCatalog();
        $module = new Ps_Languageselector();
        $html = $module->renderWidget();
        $this->assertInternalType('string', $html);
        $this->assertContains('language-selector', $html);
        $this->assertContains('English', $html);
        $this->assertContains('Français', $html);
        $this->assertSame('module:ps_languageselector/ps_languageselector.tpl', Module::$lastFetchTemplatePath);

        $expectedLanguages = array(
            array(
                'id_lang' => 1,
                'id_shop' => 1,
                'name' => 'English (English)',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'active' => 1,
                'name_simple' => 'English',
            ),
            array(
                'id_lang' => 2,
                'id_shop' => 1,
                'name' => 'Français (French)',
                'iso_code' => 'fr',
                'language_code' => 'fr-fr',
                'active' => 1,
                'name_simple' => 'Français',
            ),
        );
        $expectedCurrent = array(
            'id_lang' => 2,
            'name' => 'Français (French)',
            'name_simple' => 'Français',
            'iso_code' => 'fr',
        );
        $this->assertSame($expectedLanguages, $module->smarty->vars['languages']);
        $this->assertSame($expectedCurrent, $module->smarty->vars['current_language']);
    }

    public function testRendersForThreeLanguages()
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
                'active' => 1,
            ),
        ));
        $context = Context::getInstance();
        $context->shop->id = 1;
        $module = new Ps_Languageselector();
        $html = $module->renderWidget();
        $this->assertInternalType('string', $html);
        $this->assertContains('English', $html);
        $this->assertContains('Français', $html);
        $this->assertContains('Deutsch', $html);
    }
}
