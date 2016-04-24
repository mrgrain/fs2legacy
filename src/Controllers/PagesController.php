<?php
namespace Frogsystem\Legacy\Controllers;

use Frogsystem\Legacy\Bridge\Controllers\PageController;
use Frogsystem\Legacy\Bridge\Services\Config;
use Frogsystem\Metamorphosis\Response\View;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PageController
 * @package Frogsystem\Legacy\Controllers
 */
class PagesController extends PageController
{
    /**
     * @var string
     */
    protected $pagePath = __DIR__ . "/../pages";

    /**
     * @param View $response
     * @return ResponseInterface
     * @throws \Exception
     */
    function index(View $response)
    {
        $goto = $this->config->cfg('home_real');
        $this->config->setConfig('goto', $goto);
        $this->config->setConfig('env', 'goto', $goto);
        return $this->page($response);
    }

    /**
     * @param View $response
     * @param $name
     * @return ResponseInterface
     * @throws \Exception
     */
    function articles(View $response, $name)
    {
        $this->config->setConfig('goto', $name);
        $this->config->setConfig('env', 'goto', $name);
        return $this->display($response, $this->getPageContent('articles'));
    }
}
