<?php
namespace Frogsystem\Legacy;

use Aura\Router\Map;
use Frogsystem\Legacy\Bridge\Controllers\AdminController;
use Frogsystem\Legacy\Bridge\Services\Config;
use Frogsystem\Legacy\Controllers\PageController;
use Frogsystem\Metamorphosis\Providers\RoutesProvider;
use Frogsystem\Metamorphosis\Response\View;

/**
 * Class Routes
 * @package Frogsystem\Metamorphosis
 */
class Routes extends RoutesProvider
{
    /**
     * @var string
     */
    protected $controller = PageController::class;

    /**
     * @var string
     */
    protected $method = 'page';

    /**
     * Add the legacy route
     * @param Map $map
     * @return mixed|void
     */
    public function registerRoutes(Map $map)
    {
        $map->attach('legacy.', '/', function (Map $map) {

            // Admin
            //$map->get('admin.index', 'admin/', $this->controller(AdminController::class, 'index'))->allows(['POST']);
            $map->get('admin.assets', 'admin/assets/{asset}', $this->controller(AdminController::class, 'assets'))
                ->tokens(['asset' => '.+']);
            $map->get('admin.page', 'admin/', $this->controller(AdminController::class, 'index'))->allows(['POST'])
                ->wildcard('page');

            // Index
            $map->get('index', '', $this->controller(PageController::class, 'index'))->allows(['POST']);

            // Named article
            $map->get('article', '{name}.html', $this->controller(PageController::class, 'articles'))
                ->tokens(['name' => '[^/.]+'])
                ->allows(['POST']);

            // Pages
            $map->get('affiliates', 'affiliates/', $this->page('affiliates'))->allows(['POST']);
            $map->get('articles', 'articles/', $this->page('articles'))->allows(['POST']);
            $map->get('captcha', 'captcha/', $this->page('captcha'))->allows(['POST']);
            $map->get('comments', 'comments/', $this->page('comments'))->allows(['POST']);
            $map->get('confirm', 'confirm/', $this->page('confirm'))->allows(['POST']);
            $map->get('dlfile', 'dlfile/', $this->page('dlfile'))->allows(['POST']);
            $map->get('download', 'download/', $this->page('download'))->allows(['POST']);
            $map->get('feed', 'feed/', $this->page('feed'))->allows(['POST']);
            $map->get('gallery', 'gallery/', $this->page('gallery'))->allows(['POST']);
            $map->get('login', 'login/', $this->page('login'))->allows(['POST']);
            $map->get('logout', 'logout/', $this->page('logout'))->allows(['POST']);
            $map->get('news', 'news/', $this->page('news'))->allows(['POST']);
            $map->get('news_search', 'news_search/', $this->page('news_search'))->allows(['POST']);
            $map->get('polls', 'polls/', $this->page('polls'))->allows(['POST']);
            $map->get('press', 'press/', $this->page('press'))->allows(['POST']);
            $map->get('register', 'register/', $this->page('register'))->allows(['POST']);
            $map->get('search', 'search/', $this->page('search'))->allows(['POST']);
            $map->get('shop', 'shop/', $this->page('shop'))->allows(['POST']);
            $map->get('style_selection/', 'style_selection/', $this->page('style_selection'))->allows(['POST']);
            $map->get('user', 'user/', $this->page('user'))->allows(['POST']);
            $map->get('user_edit', 'user_edit/', $this->page('user_edit'))->allows(['POST']);
            $map->get('user_list', 'user_list/', $this->page('user_list'))->allows(['POST']);
            $map->get('viewer', 'viewer/', $this->page('viewer'))->allows(['POST']);
        });
    }

    /**
     * Helper method for displaying old pages
     * @param $name
     * @return \Closure
     */
    public function page($name)
    {
        // Return closure
        return function (View $response, Config $config) use ($name) {
            // set old config
            $config->setConfig('goto', $name);
            $config->setConfig('env', 'goto', $name);

            // call controller method
            $controller = $this->app->make($this->controller);
            return $controller->{$this->method}($response);
        };
    }
}
