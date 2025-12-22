<?php

namespace App\Core;

use App\Controllers\UserController;

class Router
{
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $userController = new UserController();

        if ($method === 'GET' && $uri === '/ping') {
            $userController->ping();
            return;
        }

        if ($method === 'GET' && $uri === '/users') {
            $controller = new \App\Controllers\UserController();

        }


        Response::error('Endpoint not found', 404);
    }
}
