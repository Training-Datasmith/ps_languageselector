<?php

class Module
{
    /** @var array */
    private static $installed = array();

    /** @var array */
    private static $instances = array();

    /** @var bool */
    public static $strictGetInstance = false;

    /** @var string|null */
    public static $lastFetchTemplatePath = null;

    /** @var bool */
    public static $forbidAssignAndFetch = false;

    public $name;
    public $tab;
    public $author;
    public $version;
    public $need_instance;
    public $displayName;
    public $description;
    public $ps_versions_compliancy = array();

    /** @var Context */
    public $context;

    /** @var TestSmartyData */
    public $smarty;

    public function __construct()
    {
        $this->context = Context::getInstance();
        if (is_object($this->context->smarty)) {
            $this->smarty = $this->context->smarty->createData($this->context->smarty);
        }
    }

    public static function resetRegistry()
    {
        self::$installed = array();
        self::$instances = array();
        self::$strictGetInstance = false;
        self::$lastFetchTemplatePath = null;
        self::$forbidAssignAndFetch = false;
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public static function isInstalled($moduleName)
    {
        return !empty(self::$installed[$moduleName]);
    }

    /**
     * @param string $moduleName
     * @param mixed  $instance
     */
    public static function registerInstance($moduleName, $instance)
    {
        self::$instances[$moduleName] = $instance;
        self::$installed[$moduleName] = true;
    }

    /**
     * @param string $moduleName
     */
    public static function setInstalled($moduleName, $installed)
    {
        if ($installed) {
            self::$installed[$moduleName] = true;
        } else {
            unset(self::$installed[$moduleName]);
        }
    }

    /**
     * @param string $moduleName
     *
     * @return Module|false
     */
    public static function getInstanceByName($moduleName)
    {
        if (self::$strictGetInstance) {
            throw new RuntimeException('getInstanceByName must not be called: ' . $moduleName);
        }

        if (!isset(self::$instances[$moduleName])) {
            return false;
        }

        return self::$instances[$moduleName];
    }

    /**
     * @return bool
     */
    public function install()
    {
        InstallEventLog::$events[] = 'parent';

        return InstallEventLog::$parentInstallReturn;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        InstallEventLog::$events[] = 'uninstall';
        if ($this->name !== null && $this->name !== '') {
            self::setInstalled($this->name, false);
        }

        return true;
    }

    /**
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     * @param string|null $locale
     *
     * @return string
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return TranslatorRecorder::trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param string $templatePath
     * @param mixed  $cache_id
     * @param mixed  $compile_id
     *
     * @return string
     */
    public function fetch($templatePath, $cache_id = null, $compile_id = null)
    {
        if (self::$forbidAssignAndFetch) {
            throw new RuntimeException('fetch must not be called');
        }

        self::$lastFetchTemplatePath = $templatePath;

        if ($templatePath === 'module:ps_languageselector/ps_languageselector.tpl') {
            $templatePath = dirname(dirname(__DIR__)) . '/ps_languageselector.tpl';
        }

        $template = $this->context->smarty->createTemplate(
            $templatePath,
            $cache_id,
            $compile_id,
            $this->smarty
        );

        return $template->fetch();
    }
}
