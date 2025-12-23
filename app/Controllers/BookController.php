<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BookService;
use App\Middleware\AuthMiddleware;

class BookController extends Controller
{
    private BookService $service;

    public function __construct()
    {
        $this->service = new BookService();
    }

    public function store(array $data): void
    {
        try {
            // Проверяем авторизацию
            $userId = AuthMiddleware::handle();
            
            // Проверяем обязательные поля
            if (!isset($data['title']) || empty(trim($data['title']))) {
                $this->error('Title is required', 400);
                return;
            }
            
            // Создаем книгу
            $book = $this->service->createBook($userId, $data);
            
            // Возвращаем успешный ответ
            $this->success($book, 201);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function index(): void
    {
        try {
            // Проверяем авторизацию
            $userId = AuthMiddleware::handle();
            
            // Получаем книги пользователя
            $books = $this->service->getUserBooks($userId);
            
            // Возвращаем список книг
            $this->success(['books' => $books]);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
