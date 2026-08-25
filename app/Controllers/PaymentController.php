<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Order;
use App\Models\Payment;
use RuntimeException;

final class PaymentController extends Controller
{
    private Order $orders;

    private Payment $payments;

    public function __construct()
    {
        $this->orders = new Order();
        $this->payments = new Payment();
    }

    /**
     * نمایش صفحه پرداخت
     */
    public function index(string $orderId): void
    {
        $user = $this->requireAuth();

        $id = (int) $orderId;

        if ($id <= 0) {
            $this->notFound();
        }

        $order = $this->orders->findById(
            $id,
            (int) $user['id']
        );

        if ($order === null) {
            $this->notFound();
        }

        /*
         * اگر سفارش قبلاً پرداخت شده باشد،
         * دیگر اجازه پرداخت مجدد نداریم.
         */
        if (
            ($order['payment_status'] ?? 'pending')
            === 'success'
        ) {
            Session::flash(
                'success',
                'این سفارش قبلاً پرداخت شده است.'
            );

            $this->redirect('/orders/' . $id);
            return;
        }

        /*
         * آخرین پرداخت سفارش را پیدا می‌کنیم.
         */
        $payment = $this->payments->findByOrderId($id);

        /*
         * اگر پرداختی وجود نداشته باشد،
         * یک پرداخت pending ایجاد می‌کنیم.
         */
        if ($payment === null) {
            $paymentId = $this->payments->create(
                $id,
                (float) $order['total']
            );

            $payment = $this->payments->findById(
                $paymentId
            );
        }

        /*
         * اگر آخرین پرداخت ناموفق بوده،
         * برای تلاش مجدد یک پرداخت کاملاً جدید ایجاد می‌کنیم.
         */
        if (
            $payment !== null
            && ($payment['status'] ?? '')
            === 'failed'
        ) {
            $paymentId = $this->payments->create(
                $id,
                (float) $order['total']
            );

            $payment = $this->payments->findById(
                $paymentId
            );
        }

        if ($payment === null) {
            throw new RuntimeException(
                'اطلاعات پرداخت ایجاد نشد.'
            );
        }

        $this->view(
            'payments/index',
            [
                'title' => 'پرداخت سفارش',
                'order' => $order,
                'payment' => $payment,
                'csrfField' => Csrf::field(),
            ]
        );
    }

    /**
     * پرداخت موفق
     */
    public function success(string $orderId): void
    {
        $user = $this->requireAuth();

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/payment/' . (int) $orderId
            );
            return;
        }

        $id = (int) $orderId;

        if ($id <= 0) {
            $this->notFound();
        }

        $order = $this->orders->findById(
            $id,
            (int) $user['id']
        );

        if ($order === null) {
            $this->notFound();
        }

        /*
         * جلوگیری از پرداخت دوباره سفارش موفق
         */
        if (
            ($order['payment_status'] ?? 'pending')
            === 'success'
        ) {
            Session::flash(
                'success',
                'این سفارش قبلاً پرداخت شده است.'
            );

            $this->redirect('/orders/' . $id);
            return;
        }

        /*
         * آخرین پرداخت سفارش را دریافت می‌کنیم.
         */
        $payment = $this->payments->findByOrderId($id);

        /*
         * اگر پرداختی وجود نداشت، ایجاد می‌کنیم.
         */
        if ($payment === null) {
            $paymentId = $this->payments->create(
                $id,
                (float) $order['total']
            );

            $payment = $this->payments->findById(
                $paymentId
            );
        }

        /*
         * اگر آخرین پرداخت failed باشد،
         * این تلاش باید با یک payment جدید انجام شود.
         */
        if (
            $payment !== null
            && ($payment['status'] ?? '')
            === 'failed'
        ) {
            $paymentId = $this->payments->create(
                $id,
                (float) $order['total']
            );

            $payment = $this->payments->findById(
                $paymentId
            );
        }

        if ($payment === null) {
            throw new RuntimeException(
                'پرداخت پیدا نشد.'
            );
        }

        /*
         * فقط پرداخت pending قابل موفق شدن است.
         */
        if (
            ($payment['status'] ?? '')
            !== 'pending'
        ) {
            Session::flash(
                'error',
                'این پرداخت دیگر قابل انجام نیست.'
            );

            $this->redirect('/orders/' . $id);
            return;
        }

        /*
         * تولید کد تراکنش شبیه‌سازی‌شده
         */
        $transactionCode =
            'SIM-'
            . date('YmdHis')
            . '-'
            . strtoupper(
                bin2hex(random_bytes(3))
            );

        /*
         * ثبت پرداخت موفق
         */
        if (
            !$this->payments->markSuccess(
                (int) $payment['id'],
                $transactionCode
            )
        ) {
            throw new RuntimeException(
                'ثبت پرداخت انجام نشد.'
            );
        }

        /*
         * تغییر وضعیت سفارش
         */
        if (
            !$this->orders->markAsPaid(
                $id,
                (int) $user['id']
            )
        ) {
            throw new RuntimeException(
                'وضعیت سفارش به‌روزرسانی نشد.'
            );
        }

        Session::flash(
            'success',
            'پرداخت با موفقیت انجام شد.'
        );

        $this->redirect(
            '/orders/' . $id
        );
    }

    /**
     * پرداخت ناموفق
     */
    public function failed(string $orderId): void
    {
        $user = $this->requireAuth();

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/payment/' . (int) $orderId
            );
            return;
        }

        $id = (int) $orderId;

        if ($id <= 0) {
            $this->notFound();
        }

        $order = $this->orders->findById(
            $id,
            (int) $user['id']
        );

        if ($order === null) {
            $this->notFound();
        }

        /*
         * اگر سفارش قبلاً پرداخت شده،
         * اجازه ثبت failed نداریم.
         */
        if (
            ($order['payment_status'] ?? 'pending')
            === 'success'
        ) {
            Session::flash(
                'success',
                'این سفارش قبلاً پرداخت شده است.'
            );

            $this->redirect('/orders/' . $id);
            return;
        }

        $payment = $this->payments->findByOrderId($id);

        /*
         * اگر payment وجود نداشت، ایجادش می‌کنیم.
         */
        if ($payment === null) {
            $paymentId = $this->payments->create(
                $id,
                (float) $order['total']
            );

            $payment = $this->payments->findById(
                $paymentId
            );
        }

        if ($payment === null) {
            throw new RuntimeException(
                'پرداخت پیدا نشد.'
            );
        }

        /*
         * فقط پرداخت pending را failed می‌کنیم.
         */
        if (
            ($payment['status'] ?? '')
            === 'pending'
        ) {
            $this->payments->markFailed(
                (int) $payment['id']
            );
        }

        /*
         * سفارش را ناموفق می‌کنیم.
         */
        $this->orders->markPaymentFailed(
            $id,
            (int) $user['id']
        );

        Session::flash(
            'error',
            'پرداخت سفارش ناموفق بود.'
        );

        $this->redirect(
            '/orders/' . $id
        );
    }

    /**
     * بررسی ورود کاربر
     */
    private function requireAuth(): array
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای پرداخت ابتدا وارد حساب کاربری شوید.'
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
     * صفحه 404
     */
    private function notFound(): never
    {
        http_response_code(404);

        echo '404 - سفارش مورد نظر پیدا نشد.';
        exit;
    }
}