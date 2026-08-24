<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Order;
use RuntimeException;

final class CheckoutController extends Controller
{
    private Cart $cart;

    private Order $orders;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->orders = new Order();
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

        $this->view('checkout/index', [
            'title' => 'تکمیل سفارش',
            'items' => $items,
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

        try {
            $orderId = $this->orders->createFromCart(
                (int) $user['id']
            );

            $order = $this->orders->findById(
                $orderId,
                (int) $user['id']
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