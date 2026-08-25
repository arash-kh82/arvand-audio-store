<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AdminDashboard;

final class AdminDashboardController extends AdminController
{
    private AdminDashboard $dashboard;

    public function __construct()
    {
        $this->dashboard = new AdminDashboard();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $this->view(
            'admin/dashboard',
            [
                'title' => 'داشبورد مدیریت',
                'statistics' => $this->dashboard->getStatistics(),
                'latestOrders' => $this->dashboard->getLatestOrders(5),
                'lowStockProducts' => $this->dashboard->getLowStockProducts(5, 5),
            ]
        );
    }
}