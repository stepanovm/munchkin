<?php


namespace Lynxx;

use Lynxx\Container\Container;
use Lynxx\Router\RouteNotFoundException;
use Lynxx\Router\Router;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Dotenv\Dotenv;

class Lynxx
{
    private static $container;

    public function __construct(Container $container)
    {
        self::$container = $container;
    }

    public static function getContainer()
    {
        return self::$container;
    }

    public function run()
    {
        $this->initSystemParams();

        /** @var Router $router */
        $router = self::$container->get(Router::class);

        $controllerClass = $router->getControllerClass();
        $actionName = $router->getActionName();
        $queryAttributes = $router->getAttributes();

        $request = $router->getRequest();
        foreach ($queryAttributes as $attribute => $value) {
            $request = $request->withAttribute($attribute, $value);
        }
        self::$container->set(RequestInterface::class, $request);


        $controller = self::$container->get($controllerClass);
        if (!$controller instanceof AbstractController) {
            throw new RouteNotFoundException('bad controller class');
        }

        $response = $controller->$actionName();

        if ($response instanceof ResponseInterface) {
            foreach ($response->getHeaders() as $k => $values) {
                foreach ($values as $v) {
                    header(sprintf('%s: %s', $k, $v), false);
                }
            }
            echo $response->getBody();
        }

    }

    public function initSystemParams()
    {
        /** System configuration */
        error_reporting(E_ALL);
        //session_start();

        $dotenv = new Dotenv(true);
        $dotenv->load(__DIR__ . '/../.env');
    }
}