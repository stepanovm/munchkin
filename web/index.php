<?php

/**
 * @author StepanovM
 *
 * Main application entry point
 */

use Lynxx\Container\Container;
use Lynxx\Lynxx;

require __DIR__ . '/../vendor/autoload.php';
$container = new Container();

$app = $container->get(Lynxx::class);

$app->run();