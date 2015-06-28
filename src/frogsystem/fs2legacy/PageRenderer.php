<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Metamorphosis\Contracts\Renderer;

class PageRenderer implements Renderer
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
        $this->data['copyright'] = get_copyright();
    }

    /**
     * Renders a view with the given data.
     * @param $view
     * @param array $data
     * @return string
     */
    public function render($view, array $data = [])
    {
        // Get Body-Template
        $template = $this->getTemplate($view);

        // extend with default data
        $data += $this->data;

        // set tags
        foreach ($data as $name => $value) {
            $template->tag($name, $value);
        }

        // Render Page
        return $this->applyFunctions(get_maintemplate((string) $template));
    }

    protected function getTemplate($view)
    {
        $template = new \template();
        $template->setFile(strtok($view, '/'));
        $template->load(strtok('/'));
        return $template;
    }

    function applyFunctions($string)
    {
        global $NAV, $SNP, $APP;

        // init globals
        $NAV = array();
        $SNP = array();
        $APP = $this->applets;

        return tpl_functions($string, $this->config->cfg('system', 'var_loop'), array(), true);
    }

    protected function loadApplets()
    {
        // Load Applets from DB
        $applet_data = $this->db->conn()->query(
            'SELECT applet_include, applet_file, applet_output
                        FROM ' . $this->config->env('DB_PREFIX') . 'applets
                        WHERE `applet_active` = 1');
        $applet_data = $applet_data->fetchAll(\PDO::FETCH_ASSOC);

        // Write Applets into Array & get Applet Template

        $new_applet_data = array();
        foreach ($applet_data as $entry) {
            // prepare data
            $entry['applet_file'] .= '.php';
            settype($entry['applet_output'], 'boolean');

            // include applets & load template
            if ($entry['applet_include'] == 1) {
                $entry['applet_template'] = $this->loadApplet($entry['applet_file'], $entry['applet_output'], array());
            }

            $new_applet_data[$entry['applet_file']] = $entry;
        }

        // Return Content
        return $new_applet_data;
    }

    public static function loadApplet($file, $output, $args)
    {
        // Setup $SCRIPT Var
        unset($SCRIPT, $template);
        $SCRIPT['argc'] = array_unshift($args, $file);
        $SCRIPT['argv'] = $args;

        // include applet & load template
        ob_start();
        global $FD;
        include(static::APPLET_PATH.DIRECTORY_SEPARATOR.$file);
        $return_data = ob_get_clean();

        // Early no output return
        if (!$output) {
            return '';
        }

        // set empty str
        $template =  isset($template) ? $template : '';
        return ($return_data . $template);
    }
}
