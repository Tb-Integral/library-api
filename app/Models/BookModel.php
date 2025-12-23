<?php

declare(strict_types=1);

namespace App\Models;

class BookModel
{
    public function update(int $id, string $title, ?string $content): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE books
            SET title = :title,
                content = :content,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'content' => $content
        ]);
    }
}
