<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\External\GoogleBooksService;
use App\Services\External\MifBooksService;
use App\Services\BookService;
use App\Middleware\AuthMiddleware;

class ExternalBooksController extends Controller
{
    public function search(): void
    {
        try {
            $query = trim($_GET['q'] ?? '');

            if (empty($query)) {
                $this->error('Search query is required', 400);
                return;
            }

            $limit = min(20, max(1, (int) ($_GET['limit'] ?? 10)));

            // Ищем в обоих источниках параллельно
            $googleBooks = (new GoogleBooksService())->search($query, $limit);
            $mifBooks = (new MifBooksService())->search($query, $limit);

            $this->success([
                'query' => $query,
                'google_books' => $googleBooks,
                'mann_ivanov' => $mifBooks,
                'total' => count($googleBooks) + count($mifBooks),
            ]);

        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    public function save(array $data): void
    {
        try {
            $userId = AuthMiddleware::handle();

            // Валидация обязательных полей
            if (!isset($data['title']) || empty(trim($data['title']))) {
                $this->error('Title is required', 400);
                return;
            }

            if (!isset($data['content']) || empty(trim($data['content']))) {
                $this->error('Content is required', 400);
                return;
            }

            // Создаем книгу через BookService
            $bookService = new BookService();

            $book = $bookService->createBook($userId, [
                'title' => trim($data['title']),
                'content' => trim($data['content']),
            ]);

            // Если переданы source и external_id, сохраняем в external_books
            if (isset($data['source']) && isset($data['external_id'])) {
                $this->saveExternalBookReference(
                    $book['id'],
                    $userId,
                    $data['source'],
                    $data['external_id'],
                    $data
                );
            }

            $this->success($book, 201);

        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    private function saveExternalBookReference(
        int $bookId,
        int $userId,
        string $source,
        string $externalId,
        array $externalData = []
    ): void {
        try {
            $db = \App\Core\DB::getInstance();

            $stmt = $db->prepare("
                INSERT INTO external_books
                (user_id, external_id, title, description, url, source)
                VALUES (:user_id, :external_id, :title, :description, :url, :source)
            ");

            $title = $externalData['title'] ?? 'External Book';
            $description = $externalData['description'] ?? $externalData['content'] ?? '';
            $url = $externalData['url'] ?? '';

            $stmt->execute([
                'user_id' => $userId,
                'external_id' => $externalId,
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'source' => $source,
            ]);

            error_log("External book saved: user=$userId, source=$source, external_id=$externalId");

        } catch (\Exception $e) {
            error_log('Failed to save external book reference: ' . $e->getMessage());
        }
    }
}
