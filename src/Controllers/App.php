<?php

namespace Express\Controllers;

class App
{
    public function home($req)
    {
        echo "Home";
    }

    public function about()
    {
        echo "Sobre nós";
    }
}