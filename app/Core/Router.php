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

        if ($method === 'POST' && $uri === '/register') {
            $controller = new \App\Controllers\UserController();
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->register($data);
            return;
        }

        if ($method === 'POST' && $uri === '/login') {
            $controller = new \App\Controllers\UserController();
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->login($data);
            return;
        }
        
        Response::error('Endpoint not found', 404);
    }
}
