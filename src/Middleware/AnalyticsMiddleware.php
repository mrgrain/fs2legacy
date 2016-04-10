<?php
namespace Frogsystem\Legacy\Middleware;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Metamorphosis\Contracts\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class AnalyticsMiddleware
{
    protected $config;

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        // get response
        $response =  $next($request, $response);

        // Save statistics after all manipulations
        //count_all($this->config->cfg('goto'));
        save_visitors();
        if (!$this->config->configExists('main', 'count_referers') || $this->config->cfg('main', 'count_referers') == 1) {
            save_referer();
        }

        return $response;
    }
}
