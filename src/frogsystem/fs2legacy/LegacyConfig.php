<?php
namespace Frogsystem\Frogsystem;

class LegacyConfig {

    protected $app;
    protected $db;
    private $config = [];

    function __construct(Frogsystem2 $app)
    {
        $this->app = $app;
        $this->config['env'] = new Config\ConfigEnv();
    }


    // load config
    // use reloadConfig if you want to get the data fresh from the databse
    public function loadConfig($name) {
        // only if config not yet exists
        if (!$this->configExists($name))
            $this->config[$name] = $this->getConfigObjectFromDatabase($name);
    }

    // reload config
    private function reloadConfig($name, $data = null, $json = false) {
        // get from DB
        if (empty($data)) {
            $this->config[$name] = $this->getConfigObjectFromDatabase($name);

            // set data from input
        } else {
            $this->config[$name] = $this->createConfigObject($name, $data, $json);
        }
    }

    // load configs by hook
    public function loadConfigsByHook($hook, $reload = false) {
        // Load configs from DB
        $data = $this->app->db->conn()->prepare(
            'SELECT * FROM '.$this->app->db->getPrefix().'config
                         WHERE `config_loadhook` = ?');
        $data->execute(array($hook));
        $data = $data->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($data as $config) {
            // Load corresponding class and get config array
            if ($reload || !$this->configExists($config['config_name']))
                $this->config[$config['config_name']] = $this->createConfigObject($config['config_name'], $config['config_data'], true);
        }
    }

    // create config object
    private function createConfigObject($name, $data, $json) {
        // Load corresponding class and get config array
        $class_name = "Frogsystem\\Frogsystem\\Config\\Config" . ucfirst(strtolower($name));

        if (!class_exists($class_name)) {
            $class_name = "Frogsystem\\Frogsystem\\Config\\ConfigData";
        }

        return new $class_name($data, $json);
    }

    // create config object from db
    private function getConfigObjectFromDatabase($name) {
        // Load config from DB
        $config = $this->app->db->conn()->prepare(
            'SELECT * FROM '.$this->app->db->getPrefix().'config
                          WHERE `config_name` = ? LIMIT 1');
        $config->execute(array($name));
        $config = $config->fetch(\PDO::FETCH_ASSOC);

        // Load corresponding class and get config array
        return $this->createConfigObject($config['config_name'], $config['config_data'], true);
    }


    // get access on a config object
    public function configObject($name) {
        // Load corresponding class and get config array
        return  $this->config[$name];
    }

    // set config
    public function setConfig() {
        // default global config
        if (func_num_args() == 2) {
            $this->config['main']->setConfig(func_get_arg(0), func_get_arg(1));

            // return other configs
        } elseif (func_num_args() == 3) {
            $this->config[func_get_arg(0)]->setConfig(func_get_arg(1), func_get_arg(2));

            // error
        } else {
            Throw \Exception('Invalid Call of method "setConfig"');
        }
    }

    // set config
    public function saveConfig($name, $newdata) {
        try {
            //get original data from db
            $original_data = $this->app->db->getField('config', 'config_data', array('W' => "`config_name` = '".$name."'"));
            if (!empty($original_data))
                $original_data = json_array_decode($original_data);
            else {
                $original_data = array();
            }


            // update data
            foreach ($newdata as $key => $value) {
                $original_data[$key] = $value;
            }

            // convert back to json
            $newdata = array(
                'config_name' => $name,
                'config_data' => json_array_encode($original_data),
            );

            // save to db
            $this->app->db->save('config', $newdata, 'config_name', false);

            // Reload Data
            $this->reloadConfig($name, $newdata['config_data'], true);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // get config
    public function config() {

        // default global config
        if (func_num_args() == 1) {
            return $this->config['main']->get(func_get_arg(0));

            // return other configs
        } elseif (func_num_args() == 2) {
            return $this->config[func_get_arg(0)]->get(func_get_arg(1));

            // error
        } else {
            Throw \Exception('Invalid Call of method "config"');
        }
    }

    // Aliases
    public function cfg() {
        return call_user_func_array(array($this, 'config'), func_get_args());
    }
    public function env($arg) {
        return $this->cfg('env', $arg);
    }
    public function system($arg) {
        return $this->cfg('system', $arg);
    }
    public function info($arg) {
        return $this->cfg('info', $arg);
    }

    // config and/or key exists
    public function configExists() {

        // check for config
        if (func_num_args() == 1) {
            return isset($this->config[func_get_arg(0)]);

            // check for config-key
        } else {
            return isset($this->config[func_get_arg(0)]) && $this->config[func_get_arg(0)]->exists(func_get_arg(1));
        }
    }
}