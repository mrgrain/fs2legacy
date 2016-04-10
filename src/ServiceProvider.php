<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Legacy\Services\Session;
use Frogsystem\Legacy\Services\Text;
use Frogsystem\Metamorphosis\Contracts\RendererInterface;
use Frogsystem\Spawn\Container;

class ServiceProvider extends \Frogsystem\Metamorphosis\Providers\ServiceProvider
{
    /**
     * Registers entries with the container.
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app[Session::class] = $app->make(Session::class);

        $app[Config::class] = $app->one(Config::class);

        $app[Text::class] = $app->once(function (Config $config) use ($app) {
            $args = [];
            if ($config->configExists('main', 'language_text')) {
                $args[] = $config->config('language_text');
            }
            return $app->make(Text::class, $args);
        });

        $app[Database::class] = $app->one(Database::class);

        $app[RendererInterface::class] = $app->one(PageRenderer::class);
    }
}
