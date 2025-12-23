<?php

namespace App\Core;

use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

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
            (new \App\Controllers\AuthController())->login();
            return;
        }

        if ($method === 'GET' && $uri === '/me') {
            try {
                $token = AuthMiddleware::handle();
                Response::json(['token' => $token]);
            } catch (\Exception $e) {
                Response::error($e->getMessage(), $e->getCode() ?: 401);
            }
            return;
        }

        Response::error('Endpoint not found', 404);
    }
}
