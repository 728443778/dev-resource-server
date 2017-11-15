<?php

use Phalcon\Di\FactoryDefault;

error_reporting(E_ALL);


define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');


/**
 * The FactoryDefault Dependency Injector automatically registers
 * the services that provide a full stack framework.
 */
$di = new FactoryDefault();

/**
 * Handle routes
 */
include APP_PATH . '/config/router.php';

/**
 * Read services
 */
include APP_PATH . '/config/services.php';

/**
 * Get config service for use in inline setup below
 */
$config = $di->getConfig();

/**
 * Include Autoloader
 */
include APP_PATH . '/config/loader.php';

$httpServer = new swoole_http_server('0.0.0.0', 8888);
$httpServer->set([
    'reactor_num' => 2, //reactor thread num
    'worker_num' => 4,    //worker process num
    'backlog' => 128,   //listen backlog
    'max_request' => 50,
    'dispatch_mode' => 1,
]);

/**
 * @param $request \Swoole\Http\Request
 * @param $response \Swoole\Http\Response
 */
function handleRequest($request, $response)
{
    global $di,$config;
    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);
    $application->useImplicitView(false);
    $_SERVER = $request->server;
    if (isset($_SERVER['request_uri'])) {
        $_GET['_url'] = $_SERVER['request_uri'];
    }
    try {
        $phalconResponse = $application->handle();
        $headers = $phalconResponse->getHeaders();
        $content = $phalconResponse->getContent();
        foreach ($headers as $key=>$value) {
            $response->header($key, $value);
        }
    } catch (\Exception $exception) {
        $content = [
            'code' => $exception->getCode()
        ];
        if ($config->environment == 'dev') {
            $content = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        if ($config->debug) {
            $message = 'Catch Exception:' . $exception->getMessage() . ';trace:' . $exception->getTraceAsString();
            $application->logger->debug($message);
        }
    } finally {
        $application = null;
    }
    if (!is_string($content)) {
        $content = json_encode($content);
    }
    $response->write($content);
}

$httpServer->on('request', 'handleRequest');

$httpServer->start();