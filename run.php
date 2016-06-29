<?php

require dirname(__FILE__) . '/vendor/autoload.php';

define('BASE_PATH', dirname(__FILE__));

spl_autoload_register(function($class) {
    $classFile = str_replace('\\', '/', $class);
    $classPath = BASE_PATH . '/' . $classFile . '.php';
    if (file_exists($classPath)) {
        include $classPath;
        return true;
    }
    return false;
});


$configFile = file_exists('config.local.php') ? 'config.local.php' : 'config.php';

if (file_exists($configFile)) {
    require_once $configFile;
} else {
    throw new \LogicException('Config file not found, please create it manually.');
}

require_once 'vendor/kbjr/Git.php/Git.php';

use common\GitChecker;
use common\GitCheckerConfig;

try {
    /** @var array $config */
    if (!empty($config)) {
        $gitCheckerConfig = new GitCheckerConfig($config);
        
        $gitChecker = new GitChecker($gitCheckerConfig);
        $gitChecker->runCommand();
    } else {
        throw new \InvalidArgumentException('Config is empty, please configure it and try again!');
    }
} catch (\Throwable $t) {
    die("GitChecker error:  [{$t->getCode()}] {$t->getMessage()}");
}
