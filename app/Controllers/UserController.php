<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use App\Middleware\AuthMiddleware;
use App\Validation\Validator;
use App\Validation\UserValidation;

class UserController extends Controller
{
    private UserService $service;
    private Validator $validator;

    public function __construct()
    {
        $this->service = new UserService();
        $this->validator = new Validator();
    }

    public function ping(): void
    {
        $this->success(['status' => 'ok']);
    }

    public function register(array $data): void
    {
        if (!$this->validator->validate($data, UserValidation::registerRules())) {
            $this->error($this->validator->getFirstError(), 422);
            return;
        }

        try {
            $token = $this->service->register(
                $data['login'],
                $data['password'],
                $data['password_confirmation'] ?? ''
            );
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

    public function me(): void
    {
        try {
            $userId = AuthMiddleware::handle();

            $user = $this->service->getById($userId);

            if (!$user) {
                $this->error('User not found', 404);
                return;
            }

            $this->success($user);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    public function index(): void
    {
        try {
            $userId = AuthMiddleware::handle();

            // Опциональные параметры пагинации
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $users = $this->service->getAllUsersExcept($userId, $limit, $offset);

            $this->success(['users' => $users]);

        } catch (\Exception $e) {
            $this->error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
