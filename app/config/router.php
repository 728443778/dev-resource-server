<?php

/**
 * @var $router \Phalcon\Mvc\Router
 */
$router = $di->getRouter();

$router->add('/object/([a-z0-9A-Z_-]+)', [
    'controller' => 'object',
    'action' => 'get',
    'params' => 1
]);
$router->add('/([a-x0-9A-Z_-]+)/([a-z0-9A-Z_-]+)/(.*)', [
    'controller' => 'object',
    'action' => 'index',
    'accessId' => 1,
    'bucket' => 2,
    'object' => 3
]);

// Define your routes here

$router->handle();
