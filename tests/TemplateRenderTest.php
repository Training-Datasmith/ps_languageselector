<?php

class TemplateRenderTest extends ModuleTestCase
{
    private function renderTwoLanguageWidget()
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

        $module = new Ps_Languageselector();

        return $module->renderWidget();
    }

    /**
     * @param string $html
     *
     * @return DOMDocument
     */
    private function loadDom($html)
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $wrapped = '<?xml encoding="UTF-8"><div id="root">' . $html . '</div>';
        $loaded = $dom->loadHTML($wrapped);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $this->assertTrue($loaded);
        $this->assertSame(array(), $errors);

        return $dom;
    }

    public function testCurrentLanguageMarked()
    {
        $html = $this->renderTwoLanguageWidget();
        $dom = $this->loadDom($html);
        $xpath = new DOMXPath($dom);
        $spans = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' language-selector ')]/span");
        $this->assertSame(1, $spans->length);
        $this->assertSame('Français', trim($spans->item(0)->textContent));

        $currentItems = $xpath->query("//li[contains(concat(' ', normalize-space(@class), ' '), ' current ')]");
        $this->assertSame(1, $currentItems->length);
        $currentAnchor = $xpath->query('.//a', $currentItems->item(0))->item(0);
        $this->assertSame('https://shop.test/lang/2', $currentAnchor->getAttribute('href'));

        $nonCurrent = $xpath->query("//li[not(contains(concat(' ', normalize-space(@class), ' '), ' current '))]");
        $this->assertGreaterThan(0, $nonCurrent->length);
        foreach ($nonCurrent as $item) {
            $class = $item->getAttribute('class');
            $this->assertNotContains('current', preg_split('/\s+/', trim($class)));
        }
    }

    public function testLinksUseGetLanguageLink()
    {
        $html = $this->renderTwoLanguageWidget();
        $dom = $this->loadDom($html);
        $xpath = new DOMXPath($dom);
        $anchors = $xpath->query('//a');
        $hrefs = array();
        $texts = array();
        foreach ($anchors as $anchor) {
            $hrefs[] = $anchor->getAttribute('href');
            $texts[] = trim($anchor->textContent);
        }
        sort($hrefs);
        $this->assertSame(
            array('https://shop.test/lang/1', 'https://shop.test/lang/2'),
            $hrefs
        );
        $this->assertContains('English', $texts);
        $this->assertContains('Français', $texts);
        $this->assertNotContains('Deutsch', $texts);
    }
}
