<?php

namespace Express\Router;

use Express\Router\Http\Request;
use Express\Router\Http\Response;

class Router
{
    /** @var string */
    private $route;

    /** @var array */
    private $routes;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var string */
    private $separator;

    /** @var string */
    private static $namespace;

    public function __construct(string $baseUrl, $separator)
    {
        $this->separator = $separator;
        $this->request = new Request();
        $this->response = new Response();
        $this->route = (substr($baseUrl, -1) == "/" ? rtrim($baseUrl, "/") : $baseUrl) . $this->request->getUri();
    }

    private function addRoute(string $httpMethod, string $route, $handler)
    {
        $this->routes[$route] = [
            "httpMethod" => $httpMethod,
            "route" => $route,
            "handler" => (!is_string($handler) ? $handler : strstr($handler, $this->separator, true)),
            "method" => (!is_string($handler) ? : str_replace($this->separator, "", strstr($handler, $this->separator))),
            "args" => []
        ];
    }

    public function get(string $route, $handler)
    {
        return $this->addRoute("GET", $route, $handler);
    }

    public function namespace(string $namespace)
    {
        self::$namespace = $namespace;
    }

    public function run()
    {
        $route = $this->routes[$this->request->getUri()] ?? [];
        // Isso aqui não tá legal não mano kkkkkkkkk
        if ($route["handler"] instanceof \Closure) {
            call_user_func($route["handler"], $this->request, $this->response, array_merge($route["args"], $this->request->getQueryParams()));
            return;
        }

        $controller = self::$namespace . "\\" . $route["handler"];
        $method = $route["method"];
        
        if (class_exists($controller) && method_exists($controller, $method)) {
            $myController = new $controller;
            $myController->$method($this->request, $this->response, $route["args"]);
        }
    }
}
