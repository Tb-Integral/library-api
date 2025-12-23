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

    public function show(int $id): void
    {
        try {
            $userId = AuthMiddleware::handle();
            
            // Проверка доступа
            if (!$this->service->hasAccessToBook($id, $userId)) {
                $this->error('Book not found', 404);
                return;
            }
            
            $book = $this->service->getBookById($id, $userId);
            
            if (!$book) {
                $this->error('Book not found', 404);
                return;
            }
            
            $this->success($book);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function update(int $id, array $data): void
    {
        try {
            $userId = AuthMiddleware::handle();

            if (
                (!isset($data['title']) || trim($data['title']) === '') &&
                (!isset($data['content']))
            ) {
                $this->error('Nothing to update', 400);
                return;
            }

            $book = $this->service->updateBook($id, $userId, $data);

            if (!$book) {
                $this->error('Book not found', 404);
                return;
            }

            $this->success($book);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $userId = AuthMiddleware::handle();
            
            $deleted = $this->service->deleteBook($id, $userId);
            
            if (!$deleted) {
                $this->error('Book not found', 404);
                return;
            }
            
            $this->success(['message' => 'Book deleted']);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function restore(int $id): void
    {
        try {
            $userId = AuthMiddleware::handle();

            $restored = $this->service->restoreBook($id, $userId);

            if (!$restored) {
                $this->error('Book not found or not deleted', 404);
                return;
            }

            $this->success(['message' => 'Book restored']);

        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function share(int $bookId, array $data): void
    {
        try {
            $ownerId = AuthMiddleware::handle();
            
            if (!isset($data['user_id'])) {
                $this->error('User ID is required', 400);
                return;
            }
            
            $guestId = (int)$data['user_id'];
            
            $shared = $this->service->shareBook($bookId, $ownerId, $guestId);
            
            if (!$shared) {
                $this->error('Book not found', 404);
                return;
            }
            
            $this->success(['message' => "Access granted to user $guestId"]);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            $this->error($e->getMessage(), $statusCode);
        }
    }

    public function shared(): void
    {
        try {
            $userId = AuthMiddleware::handle();
            
            $books = $this->service->getSharedBooks($userId);
            
            $this->success(['books' => $books]);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
