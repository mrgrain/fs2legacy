<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Legacy\Services\Lang;

class AdminPageRenderer extends PageRenderer
{
    const APPLET_PATH = FS2APPLETS;

    protected $db;
    protected $config;

    public $applets = [];

    protected $data = [];

    function __construct(Database $db, Config $config)
    {
        // resources
        $this->db = $db;
        $this->config = $config;

        // Load applets
        $this->applets = $this->loadApplets();

        // set default data
    }

    /**
     * Renders a view with the given data.
     * @param $view
     * @param array $data
     * @param array $conds
     * @return string
     */
    public function render($view, array $data = [], $conds = [])
    {
        // view parts
        $file = strtok($view, '/');
        $section = strtok('/');

        // Get Body-Template
        $template = $this->getTemplate($file);

        // extend with default data
        $data += $this->data;

        // set tags
        foreach ($data as $name => $value) {
            $template->addText($name, $value);
        }
        foreach ($conds as $name => $value) {
            $template->addCond($name, $value);
        }

        // Render Page
        return $template->get($section);
    }

    protected function getTemplate($view)
    {
        // lang
        $lang = new Lang($this->config->config('language_text'), 'admin/' . $view);
        $common =  new Lang($this->config->config('language_text'), 'admin');

        $template = new \adminpage($view, $lang, $common);
        return $template;
    }
}
