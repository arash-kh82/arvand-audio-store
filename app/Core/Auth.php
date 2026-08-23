<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return is_array(Session::get('user'));
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function user(): ?array
    {
        $user = Session::get('user');
        return is_array($user) ? $user : null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        if ($user === null || !isset($user['id'])) {
            return null;
        }

        return (int) $user['id'];
    }

    public static function login(array $user): void
    {
        Session::put('user', $user);
    }

    public static function logout(): void
    {
        Session::forget('user');
        Session::forget('_flash');
    }
}