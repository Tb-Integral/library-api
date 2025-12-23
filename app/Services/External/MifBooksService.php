<?php

declare(strict_types=1);

namespace App\Services\External;

class MifBooksService
{
    private const API_URL = 'https://www.mann-ivanov-ferber.ru/book/search.ajax';

    public function search(string $query, int $limit = 10): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $url = self::API_URL . '?q=' . urlencode($query);

        try {
            $response = file_get_contents($url);

            if ($response === false) {
                error_log('MIF API request failed');
                return [];
            }

            $data = json_decode($response, true);

            if (!is_array($data)) {
                return [];
            }

            $booksData = $data['books'] ?? [];

            if (!is_array($booksData) || empty($booksData)) {
                error_log('MIF API returned empty books array for query: ' . $query);
                return [];
            }

            // Ограничиваем количество результатов
            $booksData = array_slice($booksData, 0, $limit);

            $books = [];
            foreach ($booksData as $item) {
                if (is_array($item) && !empty($item)) {
                    $books[] = $this->formatBook($item);
                }
            }

            return $books;

        } catch (\Exception $e) {
            error_log('MIF API error: ' . $e->getMessage());
            return [];
        }
    }

    private function formatBook(array $item): array
    {
        return [
            'source' => 'mann_ivanov',
            'external_id' => $item['url'] ?? ($item['id'] ?? ''),
            'title' => $item['title'] ?? 'No title',
            'authors' => [], // MIF API не возвращает авторов в этом эндпоинте
            'description' => $item['url'] ?? '', // Используем URL как описание
            'url' => $item['url'] ?? '',
            'image' => null, // В данном API нет изображений
        ];
    }
}
