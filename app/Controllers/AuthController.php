<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;

class AuthController extends Controller
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['login']) || empty($data['password'])) {
                $this->error('Login and password required', 422);
                return;
            }

            $token = $this->service->login(
                $data['login'],
                $data['password']
            );

            $this->success(['token' => $token]);
        } catch (\Throwable $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() !== 0
                ? $e->getCode()
                : 401;

            $this->error($e->getMessage(), $statusCode);
        }
    }
}
