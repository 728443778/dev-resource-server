<?php
use Phalcon\Di\FactoryDefault;

error_reporting(E_ALL);


define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

function php_error_handler($errno, $errstr, $errfile, $errline)
{
    global $application,$di;
    $message = $errstr . '=>' . $errfile. '[' . $errline. ']';
    if ($di->getConfig()->environment=='dev') {
        echo $message;
        if ($application->config->debug) {
            $application->debug->debugEnd();
        }
        exit(1);
    }
    $application->logger->error($message);
//    $application->view->setVar('message', 'Catch error:Your Request Page is not exist');
//    $content = $application->view->render('public', 'error');
    $content = [
        'code' => $errno,
    ];
    if ($application->config->debug) {
        $application->debug->debugEnd();
    }
    $application->response->setJsonContent($content);
    $application->response->setContent($content);
    $application->response->setStatusCode(401);
    $application->response->send();
}

/**
 * @param $e Exception
 */
function exception_handler($e)
{
    global $di;
    global $application;
    $content = [];
    if ($e instanceof \Phalcon\Mvc\Dispatcher\Exception) {
        $content['message'] = 'Not Found';
        $content['router'] = $application->dispatcher->getControllerName() . '/' . $application->dispatcher->getActionName();
    } else {
        $message ='Catch Exception:' . $e->getMessage() . ':' . $e->getTraceAsString();
        if ($di->getConfig()->environment=='dev') {
            echo $message;
            if ($application->config->debug) {
                $application->debug->debugEnd();
            }
            exit(1);
        }
        $application->logger->error($message);
    }
//    $application->view->setVar('message', 'Catch exception:Your Request Page is not exist');
//    $content = $application->view->render('public', 'error');
    if ($application->config->debug) {
        $application->debug->debugEnd();
    }
    $content['code'] = $e->getCode();
    $application->response->setJsonContent($content);
    $application->response->setStatusCode(402);
    $application->response->send();
}


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

    /**
     * Handle the request
     */
    $application = new \app\libs\Application($di);

    set_error_handler('php_error_handler');
    set_exception_handler('exception_handler');

    if ($config->debug) {
        $application->logger->log('Request start');
        $debug = new \app\libs\Debug();
        $application->di->set('debug',$debug);
        $debug->start();
    }

    $application->useImplicitView(false);

    $response = $application->handle();

    $response->send();

    if ($config->debug) {
        $debug->debugEnd();
    }