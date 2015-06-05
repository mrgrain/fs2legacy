<?php
namespace Frogsystem\FS2Core;

use Frogsystem\Metamorphosis\WebApp;

abstract class GlobalData extends WebApp {

    protected $dc = 0;

    // todo: remove all calls to this methods from frogsystem
    private function depcrecate($method)
    {
        $this->dc++;
        if (0 == ($this->dc % 500)) {
            //trigger_error("Use of Frogsystem2::{$method}() is deprecated. Please access via container or use DI.", E_USER_DEPRECATED);
        }
    }

    public function text($type, $tag) {
        $this->depcrecate('text');
        if (isset($this->text[$type]))
            return $this->text[$type]->get($tag);

        return null;
    }

    // get lang phrase object
    public function setPageText($obj) {
        $this->depcrecate('setPageText');
        return $this->text['page'] = $obj;
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
}
