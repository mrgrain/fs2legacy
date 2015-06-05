<?php
namespace Frogsystem\Frogsystem;

class Router
{

    protected $routes = [];
    protected $admin;
    protected $fail;
    protected $config;

    // Routing
    function __construct(LegacyConfig $config)
    {
        $this->config = $config;
        $this->fail = function () {};
    }

    public function any($route, callable $handler)
    {
        $this->routes[$route] = $handler;
    }

    public function fail(callable $handler)
    {
        $this->fail = $handler;
    }

    public function call($route)
    {
        // check recursive and backwards for matching routes
        if (isset($this->routes[$route])) {
            return $this->routes[$route]();
        }
        return false;
    }

    public function callFail($e = null)
    {
        if (!$e) {
            $e = new \Exception('Not found');
        }
        $route = $this->fail;
        return $route($e);
    }

    /**
     * @param Database $db
     * @return bool
     * @throws
     */
    public function route(Database $db)
    {
        // Run AdminCP Hack
        if (isset($_GET['admin'])) {
            return $this->matchRoute('/admin');
        }

        //check seo
        if ($this->config->cfg('url_style') == 'seo') {
            get_seo();
        }

        // Check $_GET['go']
        $this->config->setConfig('env', 'get_go_raw', isset($_GET['go']) ? $_GET['go'] : null);
        $goto = empty($_GET['go']) ? $this->config->cfg('home_real') : $_GET['go'];
        $this->config->setConfig('env', 'get_go', $goto);

        // Forward Aliases
        $goto = $this->forward_aliases($db, $goto);

        // write $goto into $global_config_arr['goto']
        $this->config->setConfig('goto', $goto);
        $this->config->setConfig('env', 'goto', $goto);

        return $this->matchRoute('/'.$goto);
    }

    protected function matchRoute($route)
    {
        // check recursive and backwards for matching routes
        $response = $this->call($route);
        if (false === $response) {
            // nothing left
            if (empty($route) || '/' == $route) {
                $this->callFail();
            }

            // call with next rest
            return $this->matchRoute('/'.implode('/', explode('/', $route, -1)));
        }

        return $response;
    }


    protected function forward_aliases(Database $db, $GOTO)
    {
        $aliases = $db->conn()->prepare(
            'SELECT alias_go, alias_forward_to FROM ' . $this->config->env('DB_PREFIX') . 'aliases
                          WHERE `alias_active` = 1 AND `alias_go` = ?');
        $aliases->execute(array($GOTO));
        $aliases = $aliases->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($aliases as $alias) {
            if ($GOTO == $alias['alias_go']) {
                $GOTO = $alias['alias_forward_to'];
            }
        }

        return $GOTO;
    }
}