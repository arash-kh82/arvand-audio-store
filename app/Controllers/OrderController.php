<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Address;
use App\Models\Order;

final class OrderController extends Controller
{
    private Order $orders;

    private Address $addresses;

    public function __construct()
    {
        $this->orders = new Order();
        $this->addresses = new Address();
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

        $userId = (int) $user['id'];

        /*
         * دریافت سفارش فقط در صورتی که متعلق
         * به کاربر فعلی باشد.
         */
        $order = $this->orders->findById(
            $orderId,
            $userId
        );

        if ($order === null) {
            $this->notFound();
        }

        /*
         * دریافت آیتم‌های سفارش
         */
        $items = $this->orders->getItems(
            $orderId
        );

        /*
         * دریافت آدرس ارسال سفارش
         */
        $address = null;

        $addressId = (int) (
            $order['address_id'] ?? 0
        );

        if ($addressId > 0) {
            $address = $this->addresses->findById(
                $addressId,
                $userId
            );
        }

        $this->view('orders/show', [
            'title' =>
                'سفارش ' . $order['order_number'],

            'order' => $order,

            'items' => $items,

            'address' => $address,
            'csrfField' => Csrf::field(),
        ]);
    }

    /**
     * لغو و حذف کامل سفارش توسط کاربر.
     */
    public function cancel(string $id): void
    {
        $user = $this->requireAuth();
        $orderId = (int) $id;

        if ($orderId <= 0) {
            $this->notFound();
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست امنیتی نامعتبر است.'
            );

            $this->redirect('/orders/' . $orderId);
        }

        try {
            $this->orders->cancelForUser(
                $orderId,
                (int) $user['id']
            );

            Session::flash(
                'success',
                'سفارش با موفقیت لغو و حذف شد و موجودی کالاها به حالت قبل برگشت.'
            );

            $this->redirect('/account');
        } catch (\Throwable $e) {
            Session::flash(
                'error',
                $e->getMessage()
            );

            $this->redirect('/orders/' . $orderId);
        }
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