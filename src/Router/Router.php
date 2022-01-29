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

    /** @var array */
    private $args;

    /** @var string */
    private static $namespace;

    public function __construct(string $baseUrl, $separator)
    {
        $this->separator = $separator;
        $this->request = new Request();
        $this->response = new Response();
        $this->route = (substr($baseUrl, -1) == "/" ? rtrim($baseUrl, "/") : $baseUrl) . $this->request->getUri();
        $this->args = [];
    }

    private function addRoute(string $httpMethod, string $route, $handler)
    {
        $patternRoute = "/{(.*?)}/";
        // prepara o padrão para a troca da variável
        if (preg_match_all($patternRoute, $route, $matches)) {
            $route = preg_replace($patternRoute, "(.*)", $route);
        }

        $this->args = $matches[1];

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
        foreach ($this->routes as $patternRoute) {
            if (preg_match_all("~" . $patternRoute["route"] . "~", $this->request->getUri(), $matches)) {
                $route = $patternRoute;
                unset($matches[0]);
                //$this->args = array_merge($this->args, $matches);
                foreach ($matches as $key => $value) {
                    $matches[$key] = $value[0];
                }

                $route["args"] = array_values($matches);
                foreach ($this->args as $key => $value) {
                    $route["args"][$value] = $route["args"][$key];
                    unset($route["args"][$key]);
                }
            } else {
                $route = $this->routes[$this->request->getUri()];
            }
        }

        if ($route["handler"] instanceof \Closure) {
            call_user_func($route["handler"], $this->request, $this->response, $route["args"]);
            return;
        }

        $controller = self::$namespace . "\\" . $route["handler"];
        $method = $route["method"];
        
        if (class_exists($controller) && method_exists($controller, $method)) {
            $myController = new $controller;
            $myController->$method($this->request, $this->response, $route["args"]);
            return;
        }

        //Erro de falta de controlador/método
    }
}
