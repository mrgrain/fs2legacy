<?php
namespace Frogsystem\Frogsystem;
use Frogsystem\Metamorphosis\WebApp;

/**
 * @property LegacyConfig config
 * @property LegacyRouter router
 * @property LegacySession session
 * @property LegacyText text
 * @property \sql db
 */
class Frogsystem2 extends WebApp
{

    public function __construct()
    {
        parent::__construct();

        require_once(__DIR__ . '/functions.php');

        // todo populate a container and set as delegate
        // todo set this as default implementation for App

        // Init the class
        $this->init();
    }


    public function init()
    {
        // Constants
        $this->setConstants();

        // Debugging aka environment
        $this->setDebugMode(FS2_DEBUG);

        // Defaults
        // env
        // router
        // db
        // config
        // modules

        //todo shorthands for aliasing
        $this->session = $this->make('Frogsystem\\Frogsystem\\LegacySession');
        $this->config = $this->make('Frogsystem\\Frogsystem\\LegacyConfig', [$this]);
        $this['Frogsystem\\Frogsystem\\LegacyConfig'] = $this->config;
        $this->router = $this->make('Frogsystem\\Frogsystem\\Router');
        $this['Frogsystem\\Frogsystem\\Router'] = $this->router;
        $this['text'] = $this->once(function() {
            $args = [];
            if ($local = $this->config->config('language_text')) {
                $args[] = $local;
            }
            return $this->make('Frogsystem\\Frogsystem\\LegacyText', $args);
        });

        // Modules
        $this->connect('Frogsystem\\FS2Core\\FS2Core');
    }

    public function run()
    {
        try {
            // TODO: Pre-Startup Hook
            $this->db = new Database(
                $this->config->env('DB_HOST'),
                $this->config->env('DB_NAME'),
                $this->config->env('DB_USER'),
                $this->config->env('DB_PASSWORD'),
                $this->config->env('DB_PREFIX')
            );
            $this->set('Frogsystem\\Frogsystem\\Database', $this->db);

            $this->config->loadConfigsByHook('startup');

            $this->text->setLocal($this->config->config('language_text'));

        } catch (\Exception $e) {
            // DB Connection failed
            $this->fail($e); // todo: somwhere else
        }

        // route urls
        $this->invoke([$this->router, 'route']);

        // Shutdown System
        $this->__destruct();
    }

    public function fail($e) {
        return $this->router->callFail($e);
    }

    public function __destruct()
    {
        // TODO: "Shutdown Hook"

        // container destructs
        $this->db->__destruct();

        // legacy destroy global
        global $FD;
        unset($FD);
    }

    protected function setConstants()
    {
        // Content Constants
        @define('FS2SOURCE',  basename(basename(basename(__DIR__)))); //Todo: add root
        @define('FS2CONTENT', FS2SOURCE);
        @define('FS2ADMIN', FS2SOURCE.'/lib/frogsystem/fs2core/src/admin');
        @define('FS2CONFIG', FS2SOURCE.'/config');
        @define('FS2LANG', FS2SOURCE.'/lang');
        @define('FS2APPLETS', FS2SOURCE . '/lib/frogsystem/fs2core/src/applets');
        @define('FS2MEDIA', FS2CONTENT.'/media');
        @define('FS2STYLES', FS2CONTENT.'/styles');
        @define('FS2UPLOAD', FS2CONTENT.'/upload');

        // Defaults for other constants
        @define('IS_SATELLITE', false);
        @define('FS2_DEBUG', false);
        @define('FS2_ENV', 'development');
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