<?php

use Express\Router\Router;

require __DIR__ . "/vendor/autoload.php";

$router = new Router(CONF_URL_BASE, ":");
$router->namespace("Express\Controllers");

$router->get("/", "App:home");
$router->get("/sobre", function ($req, $res, $args) {
    echo "Sobre nós";
    var_dump($args);
});

$router->run();

// echo "<pre>";
// print_r($router->getRoutes());
// echo "</pre>";
