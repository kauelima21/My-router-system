<?php

namespace Express\Router\Http;

class Request
{
    private $httpMethod;
    
    private $uri;
    
    private $headers;

    /** @var array */
    private $queryParams;

    /** @var array */
    private $postVars;

    public function __construct()
    {
        $this->headers = getallheaders();
        $this->httpMethod = $_SERVER["REQUEST_METHOD"];
        $this->queryParams = $_GET;
        $this->postVars = $_POST;
        $this->setUri();
    }

    private function setUri()
    {
        $this->uri = $_SERVER["REQUEST_URI"];
        //$this->uri = explode("?", $this->uri[0]);
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getPostVars()
    {
        return $this->postVars;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getMethod()
    {
        return $this->httpMethod;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
