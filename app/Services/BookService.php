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
            'content' => $content,
        ]);

        $bookId = (int) $this->db->lastInsertId();

        return $this->getBookById($bookId, $userId);
    }

    public function getBookById(int $bookId, int $userId): ?array
    {
        // Проверка доступа
        if (!$this->hasAccessToBook($bookId, $userId)) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT id, user_id, title, content, created_at, updated_at
            FROM books
            WHERE id = :id
        ");

        $stmt->execute(['id' => $bookId]);
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
            'content' => $content,
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
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function shareBook(int $bookId, int $ownerId, int $guestId): bool
    {
        // 1. Проверить, что книга существует и принадлежит owner
        if (!$this->isOwner($bookId, $ownerId)) {
            return false;
        }


        // 2. Проверить, что guest существует
        $userService = new UserService();
        $guest = $userService->getUserById($guestId);
        if (!$guest) {
            throw new \Exception('User not found', 404);
        }

        // 3. Проверить, что не пытаемся поделиться с собой
        if ($ownerId === $guestId) {
            throw new \Exception('Cannot share with yourself', 400);
        }

        // 4. Проверить, что доступ еще не выдан
        $stmt = $this->db->prepare("
            SELECT id FROM shared_access
            WHERE owner_id = :owner_id AND guest_id = :guest_id
        ");
        $stmt->execute([
            'owner_id' => $ownerId,
            'guest_id' => $guestId,
        ]);

        if ($stmt->fetch()) {
            throw new \Exception('Access already granted', 409);
        }

        // 5. Выдать доступ
        $stmt = $this->db->prepare("
            INSERT INTO shared_access (owner_id, guest_id)
            VALUES (:owner_id, :guest_id)
        ");

        return $stmt->execute([
            'owner_id' => $ownerId,
            'guest_id' => $guestId,
        ]);
    }

    public function getSharedBooks(int $guestId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                b.id,
                b.user_id as owner_id,
                u.login as owner_login,
                b.title,
                b.content,
                b.created_at,
                b.updated_at,
                sa.created_at as shared_at
            FROM books b
            JOIN shared_access sa ON b.user_id = sa.owner_id
            JOIN users u ON b.user_id = u.id
            WHERE sa.guest_id = :guest_id
            AND b.deleted_at IS NULL
            ORDER BY sa.created_at DESC
        ");

        $stmt->execute(['guest_id' => $guestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasAccessToBook(int $bookId, int $userId): bool
    {
        // Проверить, своя ли книга
        $stmt = $this->db->prepare("
            SELECT id FROM books
            WHERE id = :book_id
            AND user_id = :user_id
            AND deleted_at IS NULL
        ");
        $stmt->execute(['book_id' => $bookId, 'user_id' => $userId]);

        if ($stmt->fetch()) {
            return true; // Своя книга
        }

        // Проверить, есть ли доступ через shared_access
        $stmt = $this->db->prepare("
            SELECT sa.id
            FROM shared_access sa
            JOIN books b ON b.user_id = sa.owner_id
            WHERE b.id = :book_id
            AND sa.guest_id = :user_id
            AND b.deleted_at IS NULL
        ");
        $stmt->execute(['book_id' => $bookId, 'user_id' => $userId]);

        return (bool) $stmt->fetch();
    }

    protected function isOwner(int $bookId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM books
            WHERE id = :book_id
            AND user_id = :user_id
            AND deleted_at IS NULL
        ");
        $stmt->execute([
            'book_id' => $bookId,
            'user_id' => $userId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function restoreBook(int $bookId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE books
            SET deleted_at = NULL,
                updated_at = NOW()
            WHERE id = :id
            AND user_id = :user_id
            AND deleted_at IS NOT NULL
        ");

        $stmt->execute([
            'id' => $bookId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
