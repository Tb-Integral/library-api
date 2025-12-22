<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Тестовый эндпоинт
        if ($method === 'GET' && $uri === '/ping') {
            echo json_encode([
                'status' => 'ok'
            ]);
            return;
        }

        // Если маршрут не найден
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint not found'
        ]);
    }
}
