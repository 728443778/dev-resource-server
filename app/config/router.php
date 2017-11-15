<?php

/**
 * @var $router \Phalcon\Mvc\Router
 */
$router = $di->getRouter();

$router->addPost('/send-sms', 'index::sendSms');
$router->addPost('/register', 'index::register');
$router->addPost('/login', 'index::login');
$router->addPost('/app-login', 'index::appLogin');

// Define your routes here

$router->handle();
