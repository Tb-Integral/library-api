<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public static function handle(): int
    {
        $headers = getallheaders();

        $authHeader
            = $headers['Authorization']
            ?? $headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? null;

        if (!$authHeader) {
            throw new \Exception('Authorization header missing', 401);
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new \Exception('Invalid authorization format', 401);
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode(
                $token,
                new Key($_ENV['JWT_SECRET'], 'HS256')
            );
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired token', 401);
        }

        return (int) $decoded->sub;
    }
}
