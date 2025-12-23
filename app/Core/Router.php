<?php

declare(strict_types=1);

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

        if ($method === 'GET' && $uri === '/users') {
            (new \App\Controllers\UserController())->index();
            return;
        }

        if ($method === 'POST' && $uri === '/books') {
            $controller = new \App\Controllers\BookController();
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $controller->store($data);
            return;
        }

        if ($method === 'GET' && $uri === '/books') {
            $controller = new \App\Controllers\BookController();
            $controller->index();
            return;
        }

        if ($method === 'GET' && $uri === '/books/shared') {
            (new \App\Controllers\BookController())->shared();
            return;
        }

        if ($method === 'GET' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            (new \App\Controllers\BookController())->show((int) $matches[1]);
            return;
        }

        if ($method === 'PUT' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            (new \App\Controllers\BookController())->update((int) $matches[1], $data);
            return;
        }

        if ($method === 'PATCH' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            (new \App\Controllers\BookController())->update((int) $matches[1], $data);
            return;
        }

        if ($method === 'DELETE' && preg_match('#^/books/(\d+)$#', $uri, $matches)) {
            (new \App\Controllers\BookController())->destroy((int) $matches[1]);
            return;
        }

        if ($method === 'POST' && preg_match('#^/books/(\d+)/share$#', $uri, $matches)) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            (new \App\Controllers\BookController())->share((int) $matches[1], $data);
            return;
        }

        if ($method === 'POST' && preg_match('#^/books/(\d+)/restore$#', $uri, $matches)) {
            (new \App\Controllers\BookController())->restore((int) $matches[1]);
            return;
        }

        // Внешний поиск книг
        if ($method === 'GET' && $uri === '/external/books/search') {
            (new \App\Controllers\ExternalBooksController())->search();
            return;
        }

        // Сохранение найденной книги
        if ($method === 'POST' && $uri === '/external/books/save') {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            (new \App\Controllers\ExternalBooksController())->save($data);
            return;
        }

        Response::error('Endpoint not found', 404);
    }
}
