<?php

define('BASE_PATH', dirname(__DIR__) . '/../');
define('APP_PATH', BASE_PATH . '/app');
return new \Phalcon\Config([
    'project' => 'resource_server',
    'environment' => 'dev',     //dev,test,prod
    'debug' => true,
    'cryptSalt' => 'usdhaiuhkjagnkjasnkfklawuiahsoidjalkngkjwahdiuhawssssssduhawdguagnndakwa',
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => '127.0.0.1',
        'username'    => 'root',
        'password'    => 'password',
        'dbname'      => 'resource-server',
        'charset'     => 'utf8',
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers',
        'modelsDir'      => APP_PATH . '/models',
        'migrationsDir'  => APP_PATH . '/migrations',
        'viewsDir'       => APP_PATH . '/views',
        'pluginsDir'     => APP_PATH . '/plugins',
        'libraryDir'     => APP_PATH . '/library',
        'cacheDir'       => BASE_PATH . '/cache',

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ],
    'logger' => [
        'path'     => BASE_PATH . '/storage/logs/',
        'format'   => '%date% [%type%] %message%',
        'date'     => 'H:i:s',
        'logLevel' => \Phalcon\Logger::DEBUG,
    ],
    'redis' => [
        'cluster' =>false,
        'host' => '127.0.0.1',
        'password' => '',
        'username' => '',
        'port' => 6379,
        'index' => 6
    ],
    'mongodb' => [
        'host' => '127.0.0.1',
        'port' => '27017',
        'username' => '',
        'password' => '',
        'database' => 'user-center'
    ],
    'http-server' => [
        'host' => '0.0.0.0',
        'port' => '8888',
        'worker_num' => 8,
        'reactor_num' => 1,
        'daemon' => false,
        'log' => APP_PATH . '/http-server.log',
        'task_num' => 8,
        'worker_max_request' => 5000,
        'dispatch_mode' => 1
    ],
    'inner_ip_pattern' => '#^192\.168\.0\.[0-9]{1,3}$#',
]);
