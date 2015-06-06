<?php
namespace Frogsystem\Metamorphosis;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Metamorphosis\Providers\RoutesProvider;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Routes
 * @package Frogsystem\Metamorphosis
 */
class Routes extends RoutesProvider
{
    protected $controller = 'Frogsystem\Legacy\PageController';
    protected $method = 'page';

    /**
     * Add the legacy route
     */
    public function plugin()
    {
        // Admin
        $this->map->get('legacy.admin', '/admin', $this->controller('AdminController', 'index'))->allows(['POST']);

        // Index
        $this->map->get('legacy.index', '/', $this->controller('PageController', 'index'))->allows(['POST']);

        // Named article
        $this->map->get('legacy.article', '/{name}.html', $this->controller('PageController', 'articles'))
            ->tokens(['name' => '[^/.]+'])
            ->allows(['POST']);

        // Pages
        $this->map->get('legacy.affiliates', '/affiliates', $this->page('affiliates'))->allows(['POST']);
        $this->map->get('legacy.articles', '/articles', $this->page('articles'))->allows(['POST']);
        $this->map->get('legacy.captcha', '/captcha', $this->page('captcha'))->allows(['POST']);
        $this->map->get('legacy.comments', '/comments', $this->page('comments'))->allows(['POST']);
        $this->map->get('legacy.confirm', '/confirm', $this->page('confirm'))->allows(['POST']);
        $this->map->get('legacy.dlfile', '/dlfile', $this->page('dlfile'))->allows(['POST']);
        $this->map->get('legacy.download', '/download', $this->page('download'))->allows(['POST']);
        $this->map->get('legacy.feed', '/feed', $this->page('feed'))->allows(['POST']);
        $this->map->get('legacy.gallery', '/gallery', $this->page('gallery'))->allows(['POST']);
        $this->map->get('legacy.login', '/login', $this->page('login'))->allows(['POST']);
        $this->map->get('legacy.logout', '/logout', $this->page('logout'))->allows(['POST']);
        $this->map->get('legacy.news', '/news', $this->page('news'))->allows(['POST']);
        $this->map->get('legacy.news_search', '/news_search', $this->page('news_search'))->allows(['POST']);
        $this->map->get('legacy.polls', '/polls', $this->page('polls'))->allows(['POST']);
        $this->map->get('legacy.press', '/press', $this->page('press'))->allows(['POST']);
        $this->map->get('legacy.register', '/register', $this->page('register'))->allows(['POST']);
        $this->map->get('legacy.search', '/search', $this->page('search'))->allows(['POST']);
        $this->map->get('legacy.shop', '/shop', $this->page('shop'))->allows(['POST']);
        $this->map->get('legacy.style_selection', '/style_selection', $this->page('style_selection'))->allows(['POST']);
        $this->map->get('legacy.user', '/user', $this->page('user'))->allows(['POST']);
        $this->map->get('legacy.user_edit', '/user_edit', $this->page('user_edit'))->allows(['POST']);
        $this->map->get('legacy.user_list', '/user_list', $this->page('user_list'))->allows(['POST']);
        $this->map->get('legacy.viewer', '/viewer', $this->page('viewer'))->allows(['POST']);
    }

    public function page($name)
    {
        // Return closure
        return function (ResponseInterface $response, Config $config) use ($name) {
            // set old config
            $config->setConfig('goto', $name);
            $config->setConfig('env', 'goto', $name);

            // call controller method
            $controller = $this->app->find($this->controller);
            return $controller->$this->method($response);
        };
    }
}
