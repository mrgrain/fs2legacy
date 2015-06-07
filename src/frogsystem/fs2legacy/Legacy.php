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
    /** @var  WebApplication */
    protected $delegate;

    /** @var string The Kernel class */
    protected $kernel = 'Frogsystem\Legacy\Kernel';

    public function __construct(WebApplication $delegate = null)
    {
        parent::__construct($delegate);

        // Constants
        $this->setConstants();

        // Debugging aka environment
        $this->setDebugMode(FS2_DEBUG);

        // internals
        $this->session = $this->once(function() {
            return $this->find('Frogsystem\\Legacy\\Services\\Session');
        });

        $this->config = $this->once(function() {
            return $this->find('Frogsystem\\Legacy\\Services\\Config');
        });

        $this->text = $this->once(function() {
            return $this->find('Frogsystem\\Legacy\\Services\\Text');
        });

        $this->db = $this->once(function() {
            return $this->find('Frogsystem\\Legacy\\Services\\Database');
        });
    }

    public function run()
    {
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
        $this->delegate->set(get_called_class(), $this);
        $this->delegate->load($this->find($this->kernel));
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
