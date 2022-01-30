<?php

namespace Express\Router;

use Express\Router\Http\Request;
use Express\Router\Http\Response;

class Router
{
    /** @var string */
    private $patch;

    /** @var array */
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

    public function __construct(string $separator = ":")
    {
        $this->separator = $separator;
        $this->request = new Request();
        $this->response = new Response();
        $this->patch = (filter_input(INPUT_GET, "route", FILTER_DEFAULT) ?? "/");
        $this->args = [];
    }

    private function addRoute(string $httpMethod, string $route, $handler)
    {
        $patternRoute = "/{(.*?)}/";
        // prepara o padrão para a troca da variável
        if (preg_match_all($patternRoute, $route, $matches)) {
            $route = preg_replace($patternRoute, "(.*)", $route);
        }

        $this->routes[$httpMethod][$route] = [
            "httpMethod" => $httpMethod,
            "route" => $route,
            "handler" => (!is_string($handler) ? $handler : strstr($handler, $this->separator, true)),
            "method" => (!is_string($handler) ? : str_replace($this->separator, "", strstr($handler, $this->separator))),
            "args" => $matches[1]
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
        $route = $this->getRoute();

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

    private function parseRoute(array $route)
    {
        if (!empty($this->route)) {
            return;
        }

        if ($route["route"] === $this->patch) {
            $this->route = $route;
            return;
        }

        if (strpos($route["route"], "(.*)") && preg_match_all("~" . $route["route"] . "~", $this->patch, $matches)) {
            unset($matches[0]);
            $this->route = $route;
            $this->route["args"] = array_combine($this->route["args"], $this->parseMatches($matches));
            return;
        }
    }

    private function getRoute(): array
    {
        foreach ($this->routes[$this->request->getMethod()] as $route) {
            $this->parseRoute($route);
        }

        return $this->route;
    }

    private function parseMatches(array $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $value[0];
        }

        return $array;
    }
}
