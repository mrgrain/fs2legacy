<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Metamorphosis\Contracts\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class AliasMiddleware implements MiddlewareInterface
{
    protected $db;
    protected $config;

    function __construct(Database $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param callable $next
     */
    public function handle(ServerRequestInterface $request, callable $next)
    {
        $request = $request->withUri($this->forward_aliases($request->getUri()));
        return $next($request);
    }

    protected function forward_aliases(UriInterface $uri)
    {
        // strip beginning /
        $path = substr($uri->getPath(), 1);

        $aliases = $this->db->conn()->prepare(<<<SQL
          SELECT `alias_go`, `alias_forward_to`
          FROM `{$this->config->env('DB_PREFIX')}aliases`
          WHERE `alias_active` = 1 AND `alias_go` = ?
SQL
        );
        $aliases->execute(array($path));
        $aliases = $aliases->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($aliases as $alias) {
            if ($path == $alias['alias_go']) {
                $path = $alias['alias_forward_to'];
            }
        }

        return $uri->withPath('/'.$path);
    }
}
