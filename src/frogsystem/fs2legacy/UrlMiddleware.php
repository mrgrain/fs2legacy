<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Metamorphosis\Contracts\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlMiddleware implements MiddlewareInterface
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
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        // path
        $path = $request->getUri()->getPath();

        // check seo
        if ($this->config->cfg('url_style') == 'seo' && 1 === preg_match('~^(.+)\.html$~', $path, $seo)) {
            $_GET['seoq'] = $seo;
            get_seo();
        }

        // Check $_GET['go']
        $this->config->setConfig('env', 'get_go_raw', isset($_GET['go']) ? $_GET['go'] : null);
        if (isset($_GET['go'])) {
            // current uri
            $uri = $request->getUri()->withPath('/'.$_GET['go']);

            // Articles from DB
            $stmt = $this->db->conn()->prepare(<<<SQL
                SELECT COUNT(`article_id`) FROM
                `{$this->config->env('DB_PREFIX')}articles`
                WHERE `article_url` = ? LIMIT 0,1
SQL
            );
            $stmt->execute(array($_GET['go']));
            $num = $stmt->fetchColumn();

            // Found articles
            if ($num >= 1) {
                // rewrite URI to /{go}.html
                $uri = $request->getUri()->withPath('/'.$_GET['go'].'.html');
            }

            // rewrite URI to = /{go}
            $request = $request->withUri($uri);
        }

        return $next($response);
    }
}
