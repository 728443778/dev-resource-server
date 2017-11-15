<?php

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Using the CLI factory default services container
$di = new CliDI();

/**
 * Include Autoloader
 */
include APP_PATH . '/config/loader.php';

// Create a console application
$console = new ConsoleApp();

/**
 * Read services
 */
include APP_PATH . '/config/services.php';

$console->setDI($di);

/**
 * 因为目前 没有对配置文件进行优化，所以需要重写这个dispatcher
 */
$di->setShared('dispatcher', function (){
    $dispatcher = new \Phalcon\Cli\Dispatcher();
    $dispatcher->setNamespaceName('\app\tasks');
    return $dispatcher;
});

/**
 * Process the console arguments
 */
$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Exception $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}