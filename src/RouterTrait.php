<?php

namespace Kaue\Router;

trait RouterTrait
{
    /** @var string */
    private $patch;

    /** @var array */
    private $route;

    /** @var null|string */
    private $group;

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
    
    private function addRoute(string $httpMethod, string $route, $handler)
    {
        $patternRoute = "/{(.*?)}/";
        // prepara o padrão para a troca da variável
        if (preg_match_all($patternRoute, $route, $matches)) {
            $route = preg_replace($patternRoute, "(.*)", $route);
        }

        $this->routes[$httpMethod][$route] = [
            "httpMethod" => $httpMethod,
            "route" => $this->group . $route,
            "handler" => (!is_string($handler) ? $handler : strstr($handler, $this->separator, true)),
            "method" => (!is_string($handler) ? : str_replace($this->separator, "", strstr($handler, $this->separator))),
            "args" => $matches[1]
        ];
    }

    public function group(?string $group)
    {
        $this->group = $group;
    }

    public function namespace(string $namespace)
    {
        self::$namespace = $namespace;
    }

    public function run()
    {
        $route = $this->getRoute();

        if (!$route) {
            $this->error = 501;
            return;
        }

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
    }

    private function parseRoute(array $route)
    {
        if (!empty($this->route)) {
            return false;
        }

        if ($route["route"] === $this->patch) {
            $this->route = $route;
            return;
        }

        if (strpos($route["route"], "(.*)") && preg_match_all("~" . $route["route"] . "~", $this->patch, $matches)) {
            unset($matches[0]);
            $this->route = $route;
            $this->route["args"] = array_combine($this->route["args"], $this->getMatches($matches));
            return;
        }
    }

    private function getRoute()
    {
        if (!isset($this->routes[$this->request->getMethod()])) {
            return false;
        }

        foreach ($this->routes[$this->request->getMethod()] as $route) {
            $this->parseRoute($route);
        }

        return $this->route;
    }

    private function getMatches(array $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $value[0];
        }

        return $array;
    }

    public function redirect(string $route, int $statusCode = 200)
    {
        http_response_code($statusCode);
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
