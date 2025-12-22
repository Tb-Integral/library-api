<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class UserService
{
    private PDO $pdo;

    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $db   = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];
        $port = $_ENV['DB_PORT'] ?? '3306';

        $this->pdo = new PDO(
            "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

}
