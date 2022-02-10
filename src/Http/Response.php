<?php

namespace Kaue\Router\Http;

use function PHPSTORM_META\type;

class Response
{
    private $statusCode;

    private $headers;

    private $contentType;

    public function __construct()
    {
        $this->statusCode = 200;
        $this->contentType = "text/html";
    }

    public function send($content)
    {
        http_response_code($this->statusCode);

        echo $content;
        
        $this->sendHeaders();
    }

    public function json($content)
    {
        http_response_code($this->statusCode);
        $this->contentType = "application/json";

        if (is_array($content)) {
            echo json_encode($content);
        } else {
            echo $content;
        }
        
        $this->sendHeaders();
    }

    public function render() {}

    public function type(string $type)
    {
        $this->contentType = $type;
        return $this;
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    private function sendHeaders()
    {
        if (!empty($this->contentType)) {
            $this->headers["Content-Type"] = $this->contentType;
        }

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
    }

    public function status(int $status): Response
    {
        $this->statusCode = $status;
        return $this;
    }
}
