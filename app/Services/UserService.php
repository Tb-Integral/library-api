<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class UserService
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function register(string $login, string $password, string $passwordConfirmation): string
    {
        // Проверка подтверждения пароля
        if ($password !== $passwordConfirmation) {
            throw new \Exception('Passwords do not match', 400);
        }

        // Проверка существующего логина
        $stmt = $this->db->prepare("SELECT id FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        if ($stmt->fetch()) {
            throw new \Exception("User already exists", 409);
        }

        // Валидация сложности пароля
        if (strlen($password) < 6) {
            throw new \Exception("Password must be at least 6 characters", 400);
        }

        // Проверка длины логина
        if (strlen($login) > 50) {
            throw new \Exception("Login may not be greater than 50 characters", 400);
        }

        // Хешируем пароль
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Вставка в БД
        $stmt = $this->db->prepare("INSERT INTO users (login, password_hash) VALUES (:login, :password)");
        $stmt->execute([
            'login' => $login,
            'password' => $passwordHash,
        ]);

        $userId = (int) $this->db->lastInsertId();

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

        return $this->generateToken((int) $user['id'], $login);
    }

    private function generateToken(int $userId, string $login): string
    {
        $payload = [
            'sub' => $userId,
            'login' => $login,
            'iat' => time(),
            'exp' => time() + 3600, // 1 час
        ];

        $secret = $_ENV['JWT_SECRET'];
        return JWT::encode($payload, $secret, 'HS256');
    }

    public function getById(int $id): ?array
    {
        $pdo = DB::getInstance();

        $stmt = $pdo->prepare(
            'SELECT id, login, created_at FROM users WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function getAllUsersExcept(int $excludeUserId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT id, login, created_at
            FROM users
            WHERE id != :exclude_id
            ORDER BY login
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, login, created_at
            FROM users
            WHERE id = :id
        ");

        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}
