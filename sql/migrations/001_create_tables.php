<?php

declare(strict_types=1);

echo "Creating tables...\n";

// Получаем настройки из .env
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'] ?? '3306';

try {
    // Подключаемся к MySQL без выбора базы данных
    $pdo = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Создаем базу данных если не существует
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' ready.\n";

    // Переключаемся на созданную базу
    $pdo->exec("USE `$dbname`");

    // Таблица users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            login VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'users' created.\n";

    // Таблица books
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS books (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            deleted_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_deleted_at (deleted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'books' created.\n";

    // Таблица shared_access
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS shared_access (
            id INT PRIMARY KEY AUTO_INCREMENT,
            owner_id INT NOT NULL,
            guest_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (guest_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_shared_access (owner_id, guest_id),
            INDEX idx_owner_id (owner_id),
            INDEX idx_guest_id (guest_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'shared_access' created.\n";

    // Таблица external_books
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS external_books (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            external_id VARCHAR(100) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            url VARCHAR(500),
            source ENUM('google_books', 'mann_ivanov') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_external_book (user_id, external_id, source),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'external_books' created.\n";

} catch (PDOException $e) {
    throw new Exception("Migration failed: " . $e->getMessage());
}
