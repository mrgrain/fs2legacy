<?php
namespace Frogsystem\Legacy\Services\Config;

class ConfigInfo extends ConfigData
{

    // startup
    protected function startup()
    {
        // set canonical paramters default to null (= no paramters)
        $this->setConfig('canonical', null);
    }
}
