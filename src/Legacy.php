<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Middleware\AliasMiddleware;
use Frogsystem\Legacy\Middleware\AnalyticsMiddleware;
use Frogsystem\Legacy\Middleware\UrlMiddleware;
use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Legacy\Services\Session;
use Frogsystem\Legacy\Services\Text;
use Frogsystem\Metamorphosis\WebApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property Config config
 * @property Session session
 * @property Text text
 * @property Database db
 */
class Legacy extends WebApplication
{
    private $huggables = [
        ServiceProvider::class,
        GlobalData::class,
        Routes::class,
    ];

    protected $middleware = [
        UrlMiddleware::class,
        AliasMiddleware::class,
        AnalyticsMiddleware::class,
    ];


    /** @var  WebApplication */
    protected $delegate;


    public function __construct(WebApplication $delegate = null)
    {
        $delegate->set(Legacy::class, $this);
        $this->delegate = $delegate;
        $this->set(get_called_class(), $this);
//        $this->delegate->set() = $delegate;
//        $this->set(get_called_class(), $this);
//        parent::__construct($delegate);

        // Constants
        $this->setConstants();

        // Debugging aka environment
        $this->setDebugMode(defined('FS2_DEBUG') ? FS2_DEBUG : false);

        // load huggables
        $this->huggables = $this->load($this->huggables);
        $this->groupHug($this->huggables);
    }

    public function __invoke(ServerRequestInterface $request = null, ResponseInterface $response = null, $next = null)
    {
        // internals
        $this->session = $this->once(function () {
            return $this->get(Session::class);
        });

        $this->config = $this->once(function () {
            return $this->get(Config::class);
        });

        $this->text = $this->once(function () {
            return $this->get(Text::class);
        });

        $this->db = $this->once(function () {
            return $this->get(Database::class);
        });

        $this->config->loadConfigsByHook('startup');
        $this->text->setLocal($this->config->config('language_text'));

        // Constructor Calls
        userlogin();
        setTimezone($this->config->cfg('timezone'));
        run_cronjobs();
        set_style();

        return parent::__invoke($request, $response, $next);
    }

    protected function setConstants()
    {
        // Content Constants
        @define('FS2SOURCE', __DIR__);
        @define('FS2ADMIN', __DIR__ . '/admin');
        @define('FS2LANG', __DIR__ . '/lang');
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

    function __destruct()
    {
        // container destructs
        $this->db->__destruct();
    }
}
