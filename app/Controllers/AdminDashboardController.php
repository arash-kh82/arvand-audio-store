<?php

declare(strict_types=1);

namespace App\Controllers;

final class AdminDashboardController extends AdminController
{
    public function index(): void
    {
        $this->requireAdmin();

        $this->view('admin/dashboard', [
            'title' => 'پنل مدیریت',
        ]);
    }
}