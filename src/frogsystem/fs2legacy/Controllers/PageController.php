<?php
namespace Frogsystem\Legacy\Controllers;

use Frogsystem\Legacy\Services\Config;
use Psr\Http\Message\ResponseInterface;

class PageController
{
    protected $config;

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    function index(ResponseInterface $response)
    {
        $goto = $this->config->cfg('home_real');
        $this->$goto($response);
    }

    function articles(ResponseInterface $response, $name)
    {
        $this->config->setConfig('goto', $name);
        $this->config->setConfig('env', 'goto', $name);
        $this->page($response, 'articles');
    }

    function page(ResponseInterface $response)
    {
        // Constructor Calls
        global $APP;
        userlogin();
        setTimezone($this->config->cfg('timezone'));
        run_cronjobs();
        count_all($this->config->cfg('goto'));
        save_visitors();
        if (!$this->config->configExists('main', 'count_referers') || $this->config->cfg('main', 'count_referers') == 1) {
            save_referer();
        }
        set_style();
        $APP = load_applets();

        // Get Body-Template
        $theTemplate = new \template();
        $theTemplate->setFile('0_main.tpl');
        $theTemplate->load('MAIN');
        $theTemplate->tag('content', $this->get_content($this->config->cfg('goto')));
        $theTemplate->tag('copyright', get_copyright());

        $template_general = (string) $theTemplate;

        // Display Page
        $response->getBody()->write(tpl_functions_init(get_maintemplate($template_general)));
        return $response;
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
