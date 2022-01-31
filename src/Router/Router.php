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

    /** @var string */
    private $projectURL;

    /** @var array */
    private $routes;

    /** @var null|int */
    private $error;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var string */
    private $separator;

    /** @var string */
    private static $namespace;

    public function __construct(string $projectURL, string $separator = ":")
    {
        $this->projectURL = (substr($projectURL, "-1") == "/" ? substr($projectURL, 0, -1) : $projectURL);
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

    public function post(string $route, $handler)
    {
        return $this->addRoute("POST", $route, $handler);
    }

    public function namespace(string $namespace)
    {
        self::$namespace = $namespace;
    }

    public function run()
    {
        $route = $this->getRoute();

        if (empty($route)) {
            $this->error = 404;
            return;
        }

        if ($route["handler"] instanceof \Closure) {
            call_user_func($route["handler"], $this->request, $this->response, $route["args"]);
            return;
        }

        $controller = self::$namespace . "\\" . $route["handler"];
        $method = $route["method"];
        
        if (class_exists($controller)) {
            $myController = new $controller;
            (method_exists($controller, $method) ? $myController->$method($this->request, $this->response, $route["args"]) : $this->error = 405);
            
            return;
        }

        $this->error = 400;
        return;

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

    private function getRoute(): ?array
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

    public function redirect(string $route, int $statusCode)
    {
        if (filter_var($route, FILTER_VALIDATE_URL)) {
            header("Location: {$route}");
            exit;
        }

        $route = (substr($route, 0, 1) == "/" ? $route : "/{$route}");
        header("Location: {$this->projectURL}{$route}");
    }

    public function error()
    {
        return $this->error;
    }
}
