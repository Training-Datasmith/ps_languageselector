<?php

class Context
{
    /** @var Context */
    private static $instance;

    /** @var object */
    public $shop;

    /** @var object */
    public $language;

    /** @var TestSmarty */
    public $smarty;

    private function __construct()
    {
        $this->shop = new stdClass();
        $this->shop->id = 1;
        $this->language = new stdClass();
        $this->language->id = 1;
        $this->language->name = 'English (English)';
        $this->language->iso_code = 'en';
        $this->smarty = new TestSmarty();
    }

    /**
     * @return Context
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function reset()
    {
        self::$instance = null;
    }
}
