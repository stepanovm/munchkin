<?php

use Laminas\Diactoros\ServerRequestFactory;
use Lynxx\Router\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

return [
    'config' => function() {
        return $config = require 'config.php';
    },
    'routes' => function () {
        return $routes = require 'routes.php';
    },
    'default_request' => function() {
        return ServerRequestFactory::fromGlobals();
    },

    Router::class => function (ContainerInterface $container) {
        return new Router($container->get('default_request'), $container->get('routes'));
    },

    ServerRequestInterface::class => function (ContainerInterface $container) {
        return $container->get(RequestInterface::class);
    },

    ContainerInterface::class => function (ContainerInterface $container) {
        return $container;
    },
];