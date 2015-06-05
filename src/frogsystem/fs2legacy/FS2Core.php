<?php
namespace Frogsystem\FS2Core;

use Frogsystem\Spawn\Contracts\Container;
use Frogsystem\Spawn\Contracts\PluggableContainer;

class FS2Core extends GlobalData implements PluggableContainer
{

    function __construct(Container $container)
    {
        parent::__construct($container);

        // Legacy autoloading
        $this->registerAutoload([
            array($this, 'legacyLoader')
        ]);

        // load local dependencies

        // load pluggables
        $this->connect('Frogsystem\\FS2Core\\Routes');

        // make myself global
        global $FD;
        $FD = $this;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        // TODO: Implement run() method.
    }

    /**
     * Executed whenever a pluggable gets plugged in.
     * @return mixed
     */
    public function plugin()
    {
        // TODO: Implement plugin() method.
    }

    /**
     * Executed whenever a pluggable gets unplugged.
     * @return mixed
     */
    public function unplug()
    {
        // TODO: Implement unplug() method.
    }


    protected function registerAutoload(array $loaders)
    {
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    public function legacyLoader($classname)
    {
        $class = explode("\\", $classname);
        $filepath = __DIR__ . '/src/lib/class_' . end($class) . '.php';

        if (file_exists($filepath)) {
            return include_once($filepath);
        } else if (strtolower(substr(end($class), -9)) === 'exception') {
            return include_once(__DIR__ . '/src/lib/exceptions.php');
        }

        return false;
    }

    /**
     * This is a pseudo container and therefore uses it delegate to lookup missing entries.
     * @param string $id
     * @return mixed
     * @throws \Frogsystem\Spawn\Exceptions\NotFoundException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            return $this->delegate->get($id);
        }
        return parent::get($id);
    }

}