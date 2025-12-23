<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

class BookService
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function createBook(int $userId, array $data): array
    {
        $title = trim($data['title']);
        $content = isset($data['content']) ? trim($data['content']) : '';
        
        if (empty($title)) {
            throw new \Exception('Book title cannot be empty', 400);
        }
        
        // Проверяем максимальную длину
        if (strlen($title) > 255) {
            throw new \Exception('Title is too long (max 255 characters)', 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO books (user_id, title, content) 
            VALUES (:user_id, :title, :content)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content
        ]);
        
        $bookId = (int) $this->db->lastInsertId();
        
        return $this->getBookById($bookId, $userId);
    }

    public function getBookById(int $bookId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, user_id, title, content, created_at, updated_at 
            FROM books 
            WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
        ");
        
        $stmt->execute([
            'id' => $bookId,
            'user_id' => $userId
        ]);
        
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $book ?: null;
    }

    public function getUserBooks(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, title, content, created_at, updated_at 
            FROM books 
            WHERE user_id = :user_id AND deleted_at IS NULL 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateBook(int $bookId, int $userId, array $data): ?array
    {

        $book = $this->getBookById($bookId, $userId);
        
        if (!$book) {
            return null;
        }
        
        $title = isset($data['title']) ? trim($data['title']) : $book['title'];
        $content = isset($data['content']) ? trim($data['content']) : $book['content'];
        
        if (empty($title)) {
            throw new \Exception('Book title cannot be empty', 400);
        }
        
        if (strlen($title) > 255) {
            throw new \Exception('Title is too long (max 255 characters)', 400);
        }
        
        $stmt = $this->db->prepare("
            UPDATE books 
            SET title = :title, 
                content = :content, 
                updated_at = NOW()
            WHERE id = :id 
            AND user_id = :user_id 
            AND deleted_at IS NULL 
        ");
        
        $stmt->execute([
            'id' => $bookId,
            'user_id' => $userId,
            'title' => $title,
            'content' => $content
        ]);
        
        return $this->getBookById($bookId, $userId);
    }

    public function deleteBook(int $bookId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE books 
            SET deleted_at = NOW() 
            WHERE id = :id 
            AND user_id = :user_id 
            AND deleted_at IS NULL
        ");
        
        $stmt->execute([
            'id' => $bookId,
            'user_id' => $userId
        ]);
        
        return $stmt->rowCount() > 0;
    }
}
