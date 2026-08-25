<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;

abstract class AdminController extends Controller
{
    protected function requireAdmin(): array
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای ورود به پنل مدیریت ابتدا وارد حساب کاربری شوید.'
            );

            $this->redirect('/login');
        }

        $user = Auth::user();

        if (
            $user === null
            || !isset($user['id'])
            || (int) $user['id'] <= 0
        ) {
            Auth::logout();

            Session::flash(
                'error',
                'نشست کاربری معتبر نیست. لطفاً دوباره وارد شوید.'
            );

            $this->redirect('/login');
        }

        if (($user['role'] ?? null) !== 'admin') {
            http_response_code(403);

            echo '403 - دسترسی به پنل مدیریت مجاز نیست.';
            exit;
        }

        if (($user['status'] ?? null) !== 'active') {
            Auth::logout();

            Session::flash(
                'error',
                'حساب کاربری شما فعال نیست.'
            );

            $this->redirect('/login');
        }

        return $user;
    }
}