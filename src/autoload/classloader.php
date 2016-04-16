<?php

spl_autoload_register(function($classname) {
    $class = explode("\\", $classname);
    $filepath = __DIR__ . '/../lib/class_' . end($class) . '.php';

    if (file_exists($filepath)) {
        return include_once($filepath);
    } else if (strtolower(substr(end($class), -9)) === 'exception') {
        return include_once(__DIR__ . '/../lib/exceptions.php');
    }

    return false;
});
