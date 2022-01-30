<?php

use Express\Router\Router;

require __DIR__ . "/vendor/autoload.php";

$router = new Router(":");
$router->namespace("Express\Controllers");
$router->get("/", "App:home");
$router->get("/sobre", "App:about");
$router->get("/api/{term}", function ($req, $res, $args) {
    var_dump($args);
});
$router->get("/users/{id}/{status}", function ($req, $res, $args) {
    var_dump($args);
});

$router->run();
