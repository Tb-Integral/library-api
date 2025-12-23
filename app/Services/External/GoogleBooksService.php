<?php

declare(strict_types=1);

namespace App\Services\External;

class GoogleBooksService
{
    private const API_URL = 'https://www.googleapis.com/books/v1/volumes';

    public function search(string $query, int $limit = 10): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $url = self::API_URL . '?q=' . urlencode($query) . '&maxResults=' . $limit;

        try {
            $response = file_get_contents($url);

            if ($response === false) {
                error_log('Google Books API request failed');
                return [];
            }

            $data = json_decode($response, true);

            if (!isset($data['items']) || !is_array($data['items'])) {
                return [];
            }

            $books = [];
            foreach ($data['items'] as $item) {
                $books[] = $this->formatBook($item);
            }

            return $books;

        } catch (\Exception $e) {
            error_log('Google Books API error: ' . $e->getMessage());
            return [];
        }
    }

    private function formatBook(array $item): array
    {
        $volumeInfo = $item['volumeInfo'] ?? [];

        return [
            'source' => 'google_books',
            'external_id' => $item['id'] ?? '',
            'title' => $volumeInfo['title'] ?? 'No title',
            'authors' => $volumeInfo['authors'] ?? [],
            'description' => $volumeInfo['description'] ?? '',
            'url' => $volumeInfo['infoLink'] ?? '',
            'image' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
        ];
    }
}
