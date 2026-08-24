<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use RuntimeException;

final class CheckoutController extends Controller
{
    private Cart $cart;

    private Order $orders;

    private Address $addresses;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->orders = new Order();
        $this->addresses = new Address();
    }

    /**
     * نمایش صفحه تأیید سفارش
     */
    public function index(): void
    {
        $user = $this->requireAuth();

        $userId = (int) $user['id'];

        $items = $this->cart->getItems($userId);

        if ($items === []) {
            Session::flash(
                'error',
                'سبد خرید شما خالی است.'
            );

            $this->redirect('/cart');
        }

        $addresses = $this->addresses->getUserAddresses($userId);

        $this->view('checkout/index', [
            'title' => 'تکمیل سفارش',
            'items' => $items,
            'addresses' => $addresses,
            'totalQuantity' =>
                $this->cart->getTotalQuantity($userId),
            'itemCount' =>
                $this->cart->getItemCount($userId),
            'total' =>
                $this->cart->getTotal($userId),
            'csrfField' => Csrf::field(),
        ]);
    }

    /**
     * ثبت نهایی سفارش
     */
    public function store(): void
    {
        $user = $this->requireAuth();

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/checkout');
        }

        $userId = (int) $user['id'];

        $addressId = (int) (
            $_POST['address_id'] ?? 0
        );

        if ($addressId <= 0) {
            Session::flash(
                'error',
                'لطفاً یک آدرس برای ارسال سفارش انتخاب کنید.'
            );

            $this->redirect('/checkout');
        }

        try {
            /*
             * بررسی می‌کنیم آدرس واقعاً متعلق به همین کاربر باشد.
             */
            $address = $this->addresses->findById(
                $addressId,
                $userId
            );

            if ($address === null) {
                throw new RuntimeException(
                    'آدرس انتخاب‌شده معتبر نیست.'
                );
            }

            /*
             * ایجاد سفارش با آدرس انتخاب‌شده
             */
            $orderId = $this->orders->createFromCart(
                $userId,
                $addressId
            );

            $order = $this->orders->findById(
                $orderId,
                $userId
            );

            if ($order === null) {
                throw new RuntimeException(
                    'سفارش ثبت شد اما اطلاعات آن پیدا نشد.'
                );
            }

            Session::flash(
                'success',
                'سفارش شما با موفقیت ثبت شد.'
            );

            $this->redirect(
                '/orders/' . $orderId
            );
        } catch (RuntimeException $e) {
            Session::flash(
                'error',
                $e->getMessage()
            );

            $this->redirect('/checkout');
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
                'برای تکمیل سفارش ابتدا وارد حساب کاربری شوید.'
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
     * بررسی CSRF
     */
    private function verifyCsrf(): bool
    {
        return Csrf::validate(
            $_POST['_token'] ?? null
        );
    }
}