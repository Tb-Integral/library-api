<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserService
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function register(string $login, string $password): string
    {
        // Проверка существующего логина
        $stmt = $this->db->prepare("SELECT id FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        if ($stmt->fetch()) {
            throw new \Exception("User already exists");
        }

        // Хешируем пароль
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Вставка в БД
        $stmt = $this->db->prepare("INSERT INTO users (login, password_hash) VALUES (:login, :password)");
        $stmt->execute([
            'login' => $login,
            'password' => $passwordHash
        ]);

        $userId = (int)$this->db->lastInsertId();

        // Генерация JWT
        return $this->generateToken($userId, $login);
    }

    public function login(string $login, string $password): string
    {
        $stmt = $this->db->prepare("SELECT id, password_hash FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new \Exception("Invalid credentials");
        }

        return $this->generateToken((int)$user['id'], $login);
    }

    private function generateToken(int $userId, string $login): string
    {
        $payload = [
            'sub' => $userId,
            'login' => $login,
            'iat' => time(),
            'exp' => time() + 3600 // 1 час
        ];

        $secret = $_ENV['JWT_SECRET'];
        return JWT::encode($payload, $secret, 'HS256');
    }
}
