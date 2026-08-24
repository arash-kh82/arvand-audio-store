<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Cart;
use RuntimeException;

final class CartController extends Controller
{
    private Cart $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    /**
     * نمایش سبد خرید
     */
    public function index(): void
    {
        $user = $this->requireAuth();

        $userId = (int) $user['id'];

        $items = $this->cart->getItems($userId);

        $this->view('cart/index', [
            'title' => 'سبد خرید',
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
     * افزودن محصول به سبد
     */
    public function add(): void
    {
        $isAjax = $this->isAjaxRequest();

        if (!Auth::check()) {
            if ($isAjax) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' =>
                            'برای افزودن محصول به سبد خرید ابتدا وارد حساب کاربری شوید.',
                    ],
                    401
                );
            }

            Session::flash(
                'error',
                'برای افزودن محصول به سبد خرید ابتدا وارد حساب کاربری شوید.'
            );

            $this->redirectBack('/products');
        }

        $user = Auth::user();

        if (
            $user === null
            || !isset($user['id'])
            || (int) $user['id'] <= 0
        ) {
            if ($isAjax) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' =>
                            'نشست کاربری معتبر نیست. لطفاً دوباره وارد شوید.',
                    ],
                    401
                );
            }

            Auth::logout();

            Session::flash(
                'error',
                'نشست کاربری معتبر نیست. لطفاً دوباره وارد شوید.'
            );

            $this->redirect('/login');
        }

        if (!$this->verifyCsrf()) {
            if ($isAjax) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' =>
                            'اعتبارسنجی امنیتی نامعتبر است. صفحه را مجدداً بارگذاری کنید.',
                    ],
                    419
                );
            }

            $this->flashError(
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirectBack('/products');
        }

        $productId = (int) (
            $_POST['product_id'] ?? 0
        );

        $quantity = (int) (
            $_POST['quantity'] ?? 1
        );

        if ($productId <= 0) {
            if ($isAjax) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' =>
                            'محصول انتخاب‌شده نامعتبر است.',
                    ],
                    422
                );
            }

            $this->flashError(
                'محصول انتخاب‌شده نامعتبر است.'
            );

            $this->redirectBack('/products');
        }

        if ($quantity <= 0) {
            if ($isAjax) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' =>
                            'تعداد محصول باید بیشتر از صفر باشد.',
                    ],
                    422
                );
            }

            $this->flashError(
                'تعداد محصول باید بیشتر از صفر باشد.'
            );

            $this->redirectBack('/products');
        }

        try {
            $this->cart->add(
                (int) $user['id'],
                $productId,
                $quantity
            );

            $message =
                'محصول با موفقیت به سبد خرید اضافه شد.';

            if ($isAjax) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'cartQuantity' =>
                        $this->cart->getTotalQuantity(
                            (int) $user['id']
                        ),
                ]);
            }

            Session::flash(
                'success',
                $message
            );
        } catch (RuntimeException $e) {
            if ($isAjax) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ],
                    422
                );
            }

            Session::flash(
                'error',
                $e->getMessage()
            );
        }

        $this->redirectBack('/products');
    }

    /**
     * تغییر تعداد محصول
     */
    public function update(): void
    {
        $user = $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->flashError(
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/cart');
        }

        $productId = (int) (
            $_POST['product_id'] ?? 0
        );

        $quantity = (int) (
            $_POST['quantity'] ?? 0
        );

        if ($productId <= 0) {
            $this->flashError(
                'محصول انتخاب‌شده نامعتبر است.'
            );

            $this->redirect('/cart');
        }

        try {
            $this->cart->updateQuantity(
                (int) $user['id'],
                $productId,
                $quantity
            );

            Session::flash(
                'success',
                'تعداد محصول با موفقیت بروزرسانی شد.'
            );
        } catch (RuntimeException $e) {
            Session::flash(
                'error',
                $e->getMessage()
            );
        }

        $this->redirect('/cart');
    }

    /**
     * حذف محصول
     */
    public function remove(): void
    {
        $user = $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->flashError(
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/cart');
        }

        $productId = (int) (
            $_POST['product_id'] ?? 0
        );

        if ($productId <= 0) {
            $this->flashError(
                'محصول انتخاب‌شده نامعتبر است.'
            );

            $this->redirect('/cart');
        }

        $removed = $this->cart->remove(
            (int) $user['id'],
            $productId
        );

        Session::flash(
            $removed ? 'success' : 'error',
            $removed
                ? 'محصول از سبد خرید حذف شد.'
                : 'محصول مورد نظر در سبد خرید وجود ندارد.'
        );

        $this->redirect('/cart');
    }

    /**
     * خالی کردن سبد
     */
    public function clear(): void
    {
        $user = $this->requireAuth();

        if (!$this->verifyCsrf()) {
            $this->flashError(
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/cart');
        }

        $this->cart->clear(
            (int) $user['id']
        );

        Session::flash(
            'success',
            'سبد خرید خالی شد.'
        );

        $this->redirect('/cart');
    }

    /**
     * بررسی ورود کاربر
     */
    private function requireAuth(): array
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای استفاده از سبد خرید ابتدا وارد حساب کاربری شوید.'
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

    /**
     * تشخیص درخواست AJAX
     */
    private function isAjaxRequest(): bool
    {
        return strtolower(
            (string) (
                $_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''
            )
        ) === 'xmlhttprequest';
    }

    /**
     * ارسال پاسخ JSON
     */
    private function jsonResponse(
        array $data,
        int $status = 200
    ): never {
        http_response_code($status);

        header(
            'Content-Type: application/json; charset=UTF-8'
        );

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    /**
     * نمایش پیام خطا و بازگشت
     */
    private function flashError(string $message): void
    {
        Session::flash(
            'error',
            $message
        );
    }

    /**
     * بازگشت به صفحه قبلی
     */
    private function redirectBack(
        string $fallback
    ): never {
        $referer = (string) (
            $_SERVER['HTTP_REFERER'] ?? ''
        );

        $baseUrl = (string) (
            app_config('base_url', '')
        );

        /*
         * فقط اجازه بازگشت به صفحات داخلی
         * همین پروژه را می‌دهیم.
         */
        if (
            $referer !== ''
            && $baseUrl !== ''
            && str_starts_with(
                $referer,
                $baseUrl
            )
        ) {
            header(
                'Location: ' . $referer,
                true,
                302
            );

            exit;
        }

        $this->redirect($fallback);
    }
}