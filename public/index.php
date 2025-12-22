<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use App\Core\Router;

// Говорим клиенту, что всегда возвращаем JSON
header('Content-Type: application/json; charset=utf-8');

$router = new Router();
$router->dispatch();
