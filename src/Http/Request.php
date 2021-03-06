<?php

namespace Kaue\Router\Http;

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

    public function body()
    {
        return file_get_contents("php://input");
    }

    private function setUri()
    {
        $this->uri = $_SERVER["REQUEST_URI"];
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
