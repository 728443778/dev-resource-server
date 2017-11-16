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

// Define your routes here

$router->handle();
