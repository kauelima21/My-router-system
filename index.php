<?php

use Express\Router\Router;
use Express\Router\Http\Request;
use Express\Router\Http\Response;

require __DIR__ . "/vendor/autoload.php";

$router = new Router(CONF_URL_BASE, ":");
$router->namespace("Express\Controllers");

$router->get("/", "App:home");
$router->get("/sobre", "App:about");

$router->get("/users/{id}/{status}", function ($req, $res, $args) {
    $res->type("application/json");
    $res->send(json_encode(["id" => $args["id"], "status" => $args["status"]]));
});

$router->get("/ops/{error}", function (Request $req, Response $res, $args) use($router) {
    $res->status($args["error"])
        ->type("text/html")
        ->send("ERRO: {$args["error"]}.");
});

$router->run();

if ($router->error()) {
    $router->redirect("/ops/{$router->error()}", $router->error());
}
