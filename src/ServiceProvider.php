<?php
namespace Frogsystem\Legacy;

use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;

class ServiceProvider extends \Frogsystem\Metamorphosis\Providers\ServiceProvider
{
    /**
     * Registers entries with the container.
     */
    public function plugin()
    {
        $this->app['Frogsystem\\Legacy\\Services\\Session']
            = $this->app->make('Frogsystem\\Legacy\\Services\\Session');

        $this->app['Frogsystem\\Legacy\\Services\\Config']
            = $this->app->one('Frogsystem\\Legacy\\Services\\Config');

        $this->app['Frogsystem\\Legacy\\Services\\Text'] = $this->app->once(function(Config $config) {
            $args = [];
            if ($config->configExists('main', 'language_text')) {
                $args[] = $config->config('language_text');
            }
            return $this->app->make('Frogsystem\\Legacy\\Services\\Text', $args);
        });

        $this->app['Frogsystem\\Legacy\\Services\\Database']
            = $this->app->one('Frogsystem\Legacy\Services\Database');

        $this->app['Frogsystem\\Metamorphosis\\Contracts\\RendererInterface'] =
            $this->app->one('Frogsystem\\Legacy\\PageRenderer');
    }
}
