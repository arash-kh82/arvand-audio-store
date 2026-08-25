<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Order;
use Throwable;

final class AdminOrderController extends AdminController
{
    private Order $orders;

    public function __construct()
    {
        $this->orders = new Order();
    }

    /**
     * لیست سفارش‌ها
     */
    public function index(): void
    {
        $this->requireAdmin();

        $this->view(
            'admin/orders/index',
            [
                'title' => 'مدیریت سفارش‌ها',
                'orders' => $this->orders->getAdminOrders(),
                'csrfField' => Csrf::field(),
            ]
        );
    }

    /**
     * نمایش جزئیات سفارش
     */
    public function show($id): void
    {
        $this->requireAdmin();

        $id = (int) $id;

        if ($id <= 0) {
            Session::flash(
                'error',
                'شناسه سفارش نامعتبر است.'
            );

            $this->redirect('/admin/orders');
        }

        $order = $this->orders->findAdminById($id);

        if ($order === null) {
            Session::flash(
                'error',
                'سفارش مورد نظر پیدا نشد.'
            );

            $this->redirect('/admin/orders');
        }

        $items = $this->orders->getItems($id);

        $this->view(
            'admin/orders/show',
            [
                'title' => 'جزئیات سفارش',
                'order' => $order,
                'items' => $items,
                'csrfField' => Csrf::field(),
            ]
        );
    }

    /**
     * تغییر وضعیت سفارش
     */
    public function updateStatus($id): void
    {
        $this->requireAdmin();

        $id = (int) $id;

        if ($id <= 0) {
            Session::flash(
                'error',
                'شناسه سفارش نامعتبر است.'
            );

            $this->redirect('/admin/orders');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        $order = $this->orders->findAdminById($id);

        if ($order === null) {
            Session::flash(
                'error',
                'سفارش مورد نظر پیدا نشد.'
            );

            $this->redirect('/admin/orders');
        }

        $status = (string) (
            $_POST['status'] ?? ''
        );

        $allowedStatuses = [
            'pending',
            'paid',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            Session::flash(
                'error',
                'وضعیت سفارش نامعتبر است.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        try {
            $updated = $this->orders->updateStatus(
                $id,
                $status
            );
        } catch (Throwable $exception) {
            Session::flash(
                'error',
                'تغییر وضعیت سفارش انجام نشد.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        if (!$updated) {
            Session::flash(
                'error',
                'تغییر وضعیت سفارش انجام نشد.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        Session::flash(
            'success',
            'وضعیت سفارش با موفقیت تغییر کرد.'
        );

        $this->redirect(
            '/admin/orders/' . $id
        );
    }

    /**
     * تغییر وضعیت پرداخت
     */
    public function updatePaymentStatus($id): void
    {
        $this->requireAdmin();

        $id = (int) $id;

        if ($id <= 0) {
            Session::flash(
                'error',
                'شناسه سفارش نامعتبر است.'
            );

            $this->redirect('/admin/orders');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        $order = $this->orders->findAdminById($id);

        if ($order === null) {
            Session::flash(
                'error',
                'سفارش مورد نظر پیدا نشد.'
            );

            $this->redirect('/admin/orders');
        }

        $paymentStatus = (string) (
            $_POST['payment_status'] ?? ''
        );

        $allowedStatuses = [
            'pending',
            'success',
            'failed',
        ];

        if (
            !in_array(
                $paymentStatus,
                $allowedStatuses,
                true
            )
        ) {
            Session::flash(
                'error',
                'وضعیت پرداخت نامعتبر است.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        try {
            $updated = $this->orders->updatePaymentStatus(
                $id,
                $paymentStatus
            );
        } catch (Throwable $exception) {
            Session::flash(
                'error',
                'تغییر وضعیت پرداخت انجام نشد.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        if (!$updated) {
            Session::flash(
                'error',
                'تغییر وضعیت پرداخت انجام نشد.'
            );

            $this->redirect(
                '/admin/orders/' . $id
            );
        }

        Session::flash(
            'success',
            'وضعیت پرداخت با موفقیت تغییر کرد.'
        );

        $this->redirect(
            '/admin/orders/' . $id
        );
    }
}