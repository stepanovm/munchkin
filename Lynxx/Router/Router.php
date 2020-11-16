<?php


namespace Lynxx\Router;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private $request;
    private $routesMap;
    private $route;

    /**
     * Router constructor.
     * @param RequestInterface $request
     * @param array $routesMap
     * @throws RouteNotFoundException
     */
    public function __construct(RequestInterface $request, array $routesMap)
    {
        $this->request = $request;

        $this->routesMap = $routesMap;

        foreach ($this->routesMap as $pattern => $routeData) {
            $expr_pattern = '~^' . $pattern . '$~';
            if (preg_match($expr_pattern, $request->getUri()->getPath(), $matches)) {
                $this->route = new Route();
                $this->route->attributes = array_filter($matches, function ($match) {
                    return !is_numeric($match);
                }, ARRAY_FILTER_USE_KEY);
                $this->route->controller = $routeData[0];
                $this->route->action = $routeData[1];
                break;
            }
        }
        if(!$this->route instanceof Route){
            throw new RouteNotFoundException('route not found');
        }
    }

    public function getControllerClass(): string
    {
        return $this->route->controller;
    }

    public function getActionName(): string
    {
        return $this->route->action;
    }

    public function getAttributes(): array
    {
        return $this->route->attributes;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getRoutesMap(): array
    {
        return $this->routesMap;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }



}