<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

echo "=======================================\n";
echo "Library API Database Migrations\n";
echo "=======================================\n\n";

// Загружаем .env переменные
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Подключаем все файлы миграций из папки migrations
$migrationFiles = glob(__DIR__ . '/migrations/*.php');
sort($migrationFiles); // Сортируем по порядку

if (empty($migrationFiles)) {
    echo "No migration files found!\n";
    exit(1);
}

echo "Found " . count($migrationFiles) . " migration file(s)\n\n";

foreach ($migrationFiles as $file) {
    echo "Running: " . basename($file) . "... ";

    try {
        require_once $file;
        echo "✓ SUCCESS\n";
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n=======================================\n";
echo "All migrations completed successfully!\n";
echo "=======================================\n";
