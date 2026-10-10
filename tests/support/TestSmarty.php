<?php

class TestSmarty
{
    /** @var LinkStub */
    public $link;

    /** @var string */
    private $compileDir;

    public function __construct()
    {
        $this->link = new LinkStub();
        $this->compileDir = sys_get_temp_dir() . '/ps_lang_smarty_compile_' . getmypid();
        if (!is_dir($this->compileDir)) {
            mkdir($this->compileDir, 0777, true);
        }
    }

    /**
     * @return string
     */
    public function getCompileDir()
    {
        return $this->compileDir;
    }

    public function resetCompileDir()
    {
        if (is_dir($this->compileDir)) {
            $files = glob($this->compileDir . '/*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * @param TestSmarty|null $parent
     *
     * @return TestSmartyData
     */
    public function createData($parent = null)
    {
        return new TestSmartyData($parent === null ? $this : $parent);
    }

    /**
     * @param string $templatePath
     * @param mixed  $cacheId
     * @param mixed  $compileId
     * @param TestSmartyData $data
     *
     * @return TestSmartyTemplate
     */
    public function createTemplate($templatePath, $cacheId = null, $compileId = null, $data = null)
    {
        return new TestSmartyTemplate($this, $templatePath, $data);
    }
}

class TestSmartyData
{
    /** @var TestSmarty */
    private $parent;

    /** @var array */
    public $vars = array();

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function assign($tplVar, $value = null)
    {
        if (Module::$forbidAssignAndFetch) {
            throw new RuntimeException('assign must not be called');
        }

        if (is_array($tplVar)) {
            $this->vars = array_merge($this->vars, $tplVar);
        } else {
            $this->vars[$tplVar] = $value;
        }
    }
}

class TestSmartyTemplate
{
    /** @var TestSmarty */
    private $root;

    /** @var string */
    private $templatePath;

    /** @var TestSmartyData */
    private $data;

    public function __construct(TestSmarty $root, $templatePath, TestSmartyData $data = null)
    {
        $this->root = $root;
        $this->templatePath = $templatePath;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function fetch()
    {
        $smartyDir = getenv('SMARTY_DIR');
        if (!$smartyDir || !is_dir($smartyDir)) {
            throw new RuntimeException('SMARTY_DIR is not set or invalid');
        }

        require_once $smartyDir . '/libs/Smarty.class.php';

        $smarty = new Smarty();
        $smarty->setCompileDir($this->root->getCompileDir());
        $smarty->setCacheDir($this->root->getCompileDir());
        $smarty->left_delimiter = '{';
        $smarty->right_delimiter = '}';

        $smarty->assign('link', $this->root->link);

        if ($this->data !== null) {
            foreach ($this->data->vars as $key => $value) {
                $smarty->assign($key, $value);
            }
        }

        return $smarty->fetch($this->templatePath);
    }
}
