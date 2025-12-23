<?php

declare(strict_types=1);

namespace App\Validation;

class UserValidation
{
    public static function registerRules(): array
    {
        return [
            'login' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'confirmed'],
        ];
    }

    public static function loginRules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
