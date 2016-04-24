<?php
namespace Frogsystem\Legacy;

use Aura\Router\Map;
use Frogsystem\Legacy\Bridge\Controllers\AdminController;
use Frogsystem\Legacy\Bridge\Providers\PagesRoutesProvider;
use Frogsystem\Legacy\Bridge\Services\Config;
use Frogsystem\Legacy\Controllers\PagesController;

/**
 * Class Routes
 * @package Frogsystem\Metamorphosis
 */
class Routes extends PagesRoutesProvider
{
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
            $map->get('index', '', $this->controller(PagesController::class, 'index'))->allows(['POST']);

            // Named article
            $map->get('article', '{name}.html', $this->controller(PagesController::class, 'articles'))
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
     * @param string $controller
     * @param string $method
     * @return \Closure
     */
    public function page($name, $controller = PagesController::class, $method = 'page')
    {
        return parent::page($name, $controller, $method);
    }
}
