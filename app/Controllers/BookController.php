<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BookService;

class BookController extends Controller
{
    protected BookService $service;

    public function __construct()
    {
        $this->service = new BookService();
    }
}
