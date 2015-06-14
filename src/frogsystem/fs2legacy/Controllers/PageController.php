<?php
namespace Frogsystem\Legacy\Controllers;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Metamorphosis\Response\View;
use Psr\Http\Message\ResponseInterface;

class PageController
{
    protected $config;

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    function index(View $response)
    {
        $goto = $this->config->cfg('home_real');
        $this->config->setConfig('goto', $goto);
        $this->config->setConfig('env', 'goto', $goto);
        return $this->page($response);
    }

    function articles(View $response, $name)
    {
        $this->config->setConfig('goto', $name);
        $this->config->setConfig('env', 'goto', $name);
        return $this->page($response, 'articles');
    }

    /**
     * @param View $view
     * @param null $file Force to load a specific page file
     * @return ResponseInterface
     * @internal param ResponseInterface $response
     */
    function page(View $view, $file = null)
    {
        // Display Page
        return $view->render('0_main.tpl/MAIN', [
            'content' => $this->get_content($file ?: $this->config->cfg('goto')),
            'copyright' =>  get_copyright(),
        ]);
    }


    protected function get_content($page)
    {
        // Display Content
        $template = '';

        // Page file
        global $FD;
        include(__DIR__."/../pages/".$page.".php");

        // Return Content
        return $template;
    }
}
