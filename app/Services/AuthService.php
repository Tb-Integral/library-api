<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class AuthService
{
    public function login(string $login, string $password): string
    {
        $pdo = DB::getInstance();

        $stmt = $pdo->prepare(
            'SELECT id, login, password_hash FROM users WHERE login = :login'
        );
        $stmt->execute(['login' => $login]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new \Exception('Invalid credentials', 401);
        }

        $payload = [
            'sub' => $user['id'],
            'login' => $user['login'],
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }
}
