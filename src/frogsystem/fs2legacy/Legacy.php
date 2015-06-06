<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Legacy\Services\Session;
use Frogsystem\Legacy\Services\Text;
use Frogsystem\Metamorphosis\WebApplication;
use Frogsystem\Spawn\Contracts\PluggableInterface;

/**
 * @property Config config
 * @property Session session
 * @property Text text
 * @property Database db
 */
class Legacy extends WebApplication implements PluggableInterface
{

    public function __construct()
    {
        parent::__construct();

        // Constants
        $this->setConstants();

        // Debugging aka environment
        $this->setDebugMode(FS2_DEBUG);

        //todo shorthands for aliasing
        $this->session = $this->find('Frogsystem\\Legacy\\Services\\Session');
        $this->config
            = $this['Frogsystem\\Legacy\\Services\\Config']
            = $this->find('Frogsystem\\Legacy\\Services\\Config', [$this]);

        $this['text'] = $this->once(function() {
            $args = [];
            if ($local = $this->config->config('language_text')) {
                $args[] = $local;
            }
            return $this->make('Frogsystem\\Legacy\\Services\\Text', $args);
        });
    }

    public function run()
    {
        $this->db = new Database(
            $this->config->env('DB_HOST'),
            $this->config->env('DB_NAME'),
            $this->config->env('DB_USER'),
            $this->config->env('DB_PASSWORD'),
            $this->config->env('DB_PREFIX')
        );
        $this->set('Frogsystem\\Legacy\\Database', $this->db);

        $this->config->loadConfigsByHook('startup');

        $this->text->setLocal($this->config->config('language_text'));
    }

    /**
     * Executed whenever a pluggable gets plugged in.
     * @return mixed
     */
    public function plugin()
    {
        // Load default kernel into the application
        $this->load($this->find('Frogsystem\Legacy\Kernel'));
    }

    public function unplug()
    {
        // container destructs
        $this->db->__destruct();

        // Disconnect Pluggables
        foreach ($this->pluggables as $pluggable) {
            $pluggable->unplug();
        }
    }

    protected function setConstants()
    {
        // Content Constants
        @define('FS2SOURCE',  __DIR__);
        @define('FS2ADMIN', __DIR__.'/admin');
        @define('FS2LANG', __DIR__.'/lang');
        @define('FS2APPLETS', __DIR__ . '/applets');
    }

    protected function setDebugMode($debug)
    {
        error_reporting(0);
        // Enable error_reporting
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
        }
    }
}
