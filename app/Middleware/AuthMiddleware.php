<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;

class AuthMiddleware
{
    public static function handle(): string
    {
        $headers = getallheaders();

        $authHeader =
            $headers['Authorization']
            ?? $headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? null;

        if (!$authHeader) {
            throw new \Exception('Authorization header missing', 401);
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new \Exception('Invalid authorization format', 401);
        }

        return substr($authHeader, 7); // возвращаем JWT
    }
}
