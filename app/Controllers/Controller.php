<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class Controller
{
    protected function success(array $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

        protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function error(string $message, int $statusCode = 400): void
    {
        Response::error($message, $statusCode);
    }
}
