# My-router-system

I've created this to pratice router, request and response interfaces with PHP and start building projects with MVC

## You will need to use a .htaccess file

```
RewriteEngine On
Options All -Indexes

# ROUTER URL Rewrite
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=/$1 [L,QSA]
```

## Practical example

```

<?php

use Express\Router\Router;
use Express\Router\Http\Request;
use Express\Router\Http\Response;

require __DIR__ . "/vendor/autoload.php";

$router = new Router("http://localhost", ":"); // set the url base and the separator
$router->namespace("Express\Controllers"); //set the namespace of the Controllers

// route with class controller
$router->get("/", "App:home"); // first the class and after the separator is the method

// route with Closure controller
$router->get("/ops/{error}", function (Request $req, Response $res, $args) {
    $res->status($args["error"]) // set the status code
        ->type("text/html") // set the content-type
        ->send("ERRO: {$args["error"]}."); // the output
});

$router->run(); // run the routes

// error redirect
if ($router->error()) {
    $router->redirect("/ops/{$router->error()}");
}

```
