<?php

namespace Kaue\Router;

use Kaue\Router\Http\Request;
use Kaue\Router\Http\Response;

class Router
{
    use RouterTrait;

    public function __construct(string $projectURL, string $separator = ":")
    {
        $this->projectURL = (substr($projectURL, "-1") == "/" ? substr($projectURL, 0, -1) : $projectURL);
        $this->separator = $separator;
        $this->request = new Request();
        $this->response = new Response();
        $this->patch = (filter_input(INPUT_GET, "route", FILTER_DEFAULT) ?? "/");
        $this->args = [];
        $this->group = null;
    }

    public function get(string $route, $handler)
    {
        return $this->addRoute("GET", $route, $handler);
    }

    public function post(string $route, $handler)
    {
        return $this->addRoute("POST", $route, $handler);
    }

    public function put(string $route, $handler)
    {
        return $this->addRoute("PUT", $route, $handler);
    }

    public function delete(string $route, $handler)
    {
        return $this->addRoute("DELETE", $route, $handler);
    }
}
