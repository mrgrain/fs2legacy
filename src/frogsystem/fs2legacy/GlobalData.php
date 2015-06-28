<?php
namespace Frogsystem\Legacy;

use Frogsystem\Spawn\Container;
use Frogsystem\Spawn\Contracts\PluggableInterface;

class GlobalData extends Container implements PluggableInterface
{
    /**
     * Request Legacy Container
     * @param Legacy $container
     */
    public function __construct(Legacy $container = null)
    {
        parent::__construct($container);
    }

    /**
     * This is a pseudo container and therefore uses it delegate to lookup missing entries.
     * @param string $id
     * @return mixed
     * @throws \Frogsystem\Spawn\Exceptions\NotFoundException
     */
    function __get($id)
    {
        if (isset($this->delegate->$id)) {
            return $this->delegate->$id;
        }
        return $this->delegate->get($id);
    }

    /**
     * Executed whenever a pluggable gets plugged in.
     * @return mixed
     */
    public function plugin()
    {
        global $FD;
        $FD = $this;
    }

    /**
     * Executed whenever a pluggable gets unplugged.
     * @return mixed
     */
    public function unplug()
    {
        // legacy destroy global
        global $FD;
        unset($FD);
    }



    public function text($type, $tag) {
        $this->depcrecate('text');
        if (isset($this->text[$type]))
            return $this->text[$type]->get($tag);

        return null;
    }

    public function db() {
        $this->depcrecate('db');
        return $this->db;
    }
    public function sql() {
        $this->depcrecate('sql');
        return $this->db();
    }

    // config interface
    public function loadConfig($name)
    {
        $this->depcrecate('loadConfig');
        return $this->config->loadConfig($name);
    }
    public function configObject($name)
    {
        $this->depcrecate('configObject');
        return $this->config->configObject($name);
    }
    public function setConfig()
    {
        $this->depcrecate('setConfig');
        return call_user_func_array(array($this->config, 'setConfig'), func_get_args());
    }
    public function saveConfig($name, $newdata)
    {
        $this->depcrecate('saveConfig');
        return $this->config->saveConfig($name, $newdata);
    }
    public function config()
    {
        $this->depcrecate('config');
        return call_user_func_array(array($this->config, 'config'), func_get_args());
    }
    // Aliases
    public function cfg() {
        $this->depcrecate('cfg');
        return call_user_func_array(array($this->config, 'config'), func_get_args());
    }
    public function env($arg) {
        $this->depcrecate('env');
        return $this->config->cfg('env', $arg);
    }
    public function system($arg) {
        $this->depcrecate('system');
        return $this->config->cfg('system', $arg);
    }
    public function info($arg) {
        $this->depcrecate('info');
        return $this->config->cfg('info', $arg);
    }
    public function configExists() {
        $this->depcrecate('configExists');
        return call_user_func_array(array($this->config, 'configExists'), func_get_args());
    }

    private function depcrecate($method)
    {
    }
}
