<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Order;

final class OrderController extends Controller
{
    private Order $orders;

    public function __construct()
    {
        $this->orders = new Order();
    }

    /**
     * نمایش جزئیات یک سفارش
     */
    public function show(string $id): void
    {
        $user = $this->requireAuth();

        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->notFound();
        }

        $order = $this->orders->findById(
            $orderId,
            (int) $user['id']
        );

        if ($order === null) {
            $this->notFound();
        }

        $items = $this->orders->getItems(
            $orderId
        );

        $this->view('orders/show', [
            'title' =>
                'سفارش ' . $order['order_number'],

            'order' => $order,

            'items' => $items,
        ]);
    }

    /**
     * بررسی ورود کاربر
     */
    private function requireAuth(): array
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای مشاهده سفارش ابتدا وارد حساب کاربری شوید.'
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

        return $user;
    }

    /**
     * صفحه 404
     */
    private function notFound(): never
    {
        http_response_code(404);

        echo '404 - سفارش مورد نظر پیدا نشد.';
        exit;
    }
}