<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
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

}
