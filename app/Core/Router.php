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

        if ($method === 'POST' && $uri === '/register') {
            $controller = new \App\Controllers\UserController();
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $controller->register($data);
            return;
        }

        if ($method === 'POST' && $uri === '/login') {
            (new \App\Controllers\AuthController())->login();
            return;
        }

        if ($method === 'GET' && $uri === '/me') {
            (new \App\Controllers\UserController())->me();
            return;
        }

        if ($method === 'POST' && $uri === '/books') {
            $controller = new \App\Controllers\BookController();
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $controller->store($data);
            return;
        }

        if ($method === 'GET' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            (new \App\Controllers\BookController())->show((int)$matches[1]);
            return;
        }

        if ($method === 'GET' && $uri === '/books') {
            $controller = new \App\Controllers\BookController();
            $controller->index();
            return;
        }     
        
        if ($method === 'PUT' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            (new \App\Controllers\BookController())->update((int)$matches[1], $data);
            return;
        }

        if ($method === 'PATCH' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            (new \App\Controllers\BookController())->update((int)$matches[1], $data);
            return;
        }

        Response::error('Endpoint not found', 404);
    }
}
