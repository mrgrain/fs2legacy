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
     * @var Config
     */
    protected $config;

    /**
     * PageController constructor.
     * @param Config $config
     */
    function __construct(Config $config)
    {
        $this->config = $config;
    }

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
        return $this->page($response, 'articles');
    }

    /**
     * Display a Page file
     * @param View $view
     * @param null $file Force to load a specific page file
     * @return ResponseInterface
     * @internal param ResponseInterface $response
     */
    function page(View $view, $file = null)
    {
        return $this->display($view, $this->get_content($file ?: $this->config->cfg('goto')));
    }

    /**
     * Get content from a page file
     * @param $page
     * @return string
     */
    protected function get_content($page)
    {
        // Display Content
        $template = '';

        // Page file
        global $FD;
        include(__DIR__ . "/../pages/" . $page . ".php");

        // Return Content
        return $template;
    }
}
