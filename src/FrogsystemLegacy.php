<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Bridge\Services\Config;
use Frogsystem\Legacy\Bridge\Services\Session;
use Frogsystem\Legacy\Bridge\Services\Text;
use Frogsystem\Legacy\Polls\Services\Routes as PollsRoutes;
use Frogsystem\Metamorphosis\WebApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class FrogsystemLegacy
 * @property Session session
 * @property Config config
 * @property Text text
 * @package Frogsystem\Legacy
 */
class FrogsystemLegacy extends WebApplication
{
    /**
     * @var array
     */
    private $huggables = [
        PollsRoutes::class,
        Routes::class,
    ];

    /**
     * FrogsystemLegacy constructor.
     * @param WebApplication|null $delegate
     */
    public function __construct(WebApplication $delegate = null)
    {
        // Constants
        $this->setConstants();

        // call parent and load huggables
        parent::__construct($delegate);

        // load huggables
        $this->huggables = $this->load($this->huggables);
        $this->groupHug($this->huggables);
    }

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @param null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request = null, ResponseInterface $response = null, $next = null)
    {
        // internals
        $this->session = $this->once(function () {
            return $this->delegate->get(Session::class);
        });

        $this->config = $this->once(function () {
            return $this->delegate->get(Config::class);
        });

        $this->text = $this->once(function () {
            return $this->delegate->get(Text::class);
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

    /**
     * Set required Constants
     */
    protected function setConstants()
    {
        // Content Constants
        @define('FS2SOURCE', __DIR__);
        @define('FS2ADMIN', __DIR__ . '/admin');
        @define('FS2LANG', __DIR__ . '/lang');
        @define('FS2APPLETS', __DIR__ . '/applets');
    }
}
