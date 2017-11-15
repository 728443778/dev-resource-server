<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Flash\Direct as Flash;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ]);

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);
    if ($config->debug) {
        $eventManager = new Phalcon\Events\Manager();
        $dbEvents = new \app\events\DbEvents();
        $eventManager->attach('db', $dbEvents);
        $connection->setEventsManager($eventManager);
    }

    return $connection;
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    $config = $this->getConfig();
    return new Phalcon\Mvc\Model\MetaData\Files([
        'metaDataDir' => $config->application->cacheDir . '/metaData/'
    ]);
});

$di->setShared('modelsCache', function(){
    $frontCache = new \Phalcon\Cache\Frontend\Data([
        'lifetime' => 30
    ]);
    $cache = new \Phalcon\Cache\Backend\File($frontCache, [
        'cacheDir' => $this->getConfig()->application->cacheDir . '/modelsCache/'
    ]);
    return $cache;
});

$di->setShared('viewCache',function(){
    $config = $this->getConfig();
    $frontCache = new \Phalcon\Cache\Frontend\Output([
        'lifetime' => 86400
    ]);
    $cache = new \Phalcon\Cache\Backend\File($frontCache, [
        'cacheDir' => $config->application->cacheDir . '/viewsCache/'
    ]);
//    $cache = new \Phalcon\Cache\Backend\Memcache($frontCache, [
//        'host' => 'localhost',
//        'port' => 11211
//    ]);
    return $cache;
});

$di->setShared('redis', function (){
    $config = $this->getConfig();
    $frontCache = new Phalcon\Cache\Frontend\Data(
        [
            'lifetime' => 3600,
        ]
    );
    $redis = new \app\libs\Redis($frontCache, [
        'host' => $config->redis->host,
        'port' => $config->redis->port,
        'username' => $config->redis->username,
        'password' => $config->redis->password,
        'index' => $config->redis->index,
        'prefix' => $config->project . '-'
    ]);
    return $redis;
});

$di->setShared('mongodb', function(){
    $config = $this->getConfig();
    $host = $config->mongodb->host;
    $port = $config->mongodb->port;
    $username = $config->mongodb->username;
    $password = $config->mongodb->password;
    if (empty($username)) {
        $options = [];
    } else {
        $options = [
            'username' => $username,
            'password' => $password
        ];
    }
    $url = 'mongodb://' . $host .':' . $port;
    $client = new MongoDB\Client($url, $options);
    return $client;
});

$di->setShared('dispatcher', function(){
    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    $dispatcher->setDefaultNamespace('app\controllers');
    $dispatcher->setDefaultController('Index');
    return $dispatcher;
});

/**
 * Crypt service
 */
$di->setShared('crypt', function () {
    $config = $this->getConfig();

    $crypt = new \Phalcon\Crypt();
    $crypt->setKey($config->cryptSalt);
    return $crypt;
});

$di->setShared('security', function (){
    return new \Phalcon\Security();
});

$di->setShared('random', function(){
    return new \Phalcon\Security\Random();
});

$di->setShared('logger', function($filename = null, $format = null){
    $config = $this->getConfig();

    $format   = $format ?: $config->get('logger')->format;
//    $filename = date('Y-m-d') . '.log';
    $filename = 'app.log';
    $path     = $config->get('logger')->path . $filename;
    $filename = str_replace('\\', '/', $path);

    $formatter = new Phalcon\Logger\Formatter\Line($format, $config->get('logger')->date);
    $logger    = new \Phalcon\Logger\Adapter\Stream($filename);

    $logger->setFormatter($formatter);
    $logger->setLogLevel($config->get('logger')->logLevel);

    return $logger;
});
