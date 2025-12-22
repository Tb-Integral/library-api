<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;

class UserController extends Controller
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function ping(): void
    {
        $this->success(['status' => 'ok']);
    }

    public function register(array $data): void
    {
        if (!isset($data['login'], $data['password'])) {
            $this->error('Login and password required', 400);
            return;
        }

        try {
            $token = $this->service->register($data['login'], $data['password']);
            $this->success(['token' => $token]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function login(array $data): void
    {
        if (!isset($data['login'], $data['password'])) {
            $this->error('Login and password required', 400);
            return;
        }

        try {
            $token = $this->service->login($data['login'], $data['password']);
            $this->success(['token' => $token]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}
