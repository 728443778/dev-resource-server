<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
//$loader->registerDirs(
//    [
//        $config->application->controllersDir,
//        $config->application->modelsDir
//    ]
//)->register();

$loader->registerNamespaces([
    'app' => APP_PATH,
    'MongoDB' => BASE_PATH . '/vendor/mongodb/mongodb/src/',
    'sevenUtils' => BASE_PATH . '/vendor/728443778/php-utils/src/'
]);

/**
 * register some function classs
 */
$loader->registerFiles([
    APP_PATH . '/ErrorCode.php',
    BASE_PATH . '/vendor/mongodb/mongodb/src/functions.php',
    BASE_PATH . '/vendor/728443778/php-utils/src/ErrorCode.php'
]);

$loader->register();