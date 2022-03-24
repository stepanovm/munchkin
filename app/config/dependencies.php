<?php

use bin\Command\AppBuild\Compressor\CssCompressor;
use bin\Command\AppBuild\Compressor\JsCompressor;
use Laminas\Diactoros\ServerRequestFactory;
use Lynxx\DB;
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
    'default_request' => ServerRequestFactory::fromGlobals(),
    'compressor_Js' => function (ContainerInterface $container) {
        return new JsCompressor();
    },
    'compressor_Css' => function (ContainerInterface $container) {
        return new CssCompressor();
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

    PDO::class => function (ContainerInterface $container) {
        return DB::instance();
    },
];