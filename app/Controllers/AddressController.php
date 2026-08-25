<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Address;

final class AddressController extends Controller
{
    private Address $addresses;

    public function __construct()
    {
        $this->addresses = new Address();
    }

    public function index(): void
    {
        $user = $this->requireAuth();

        $this->view('addresses/index', [
            'title' => 'آدرس‌های من',
            'addresses' => $this->addresses->getUserAddresses(
                (int) $user['id']
            ),
            'csrfField' => Csrf::field(),
        ]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/addresses');
        }

        $title = trim(
            (string) ($_POST['title'] ?? '')
        );

        $receiverName = trim(
            (string) ($_POST['receiver_name'] ?? '')
        );

        $phone = trim(
            (string) ($_POST['phone'] ?? '')
        );

        $province = trim(
            (string) ($_POST['province'] ?? '')
        );

        $city = trim(
            (string) ($_POST['city'] ?? '')
        );

        $address = trim(
            (string) ($_POST['address'] ?? '')
        );

        $postalCode = trim(
            (string) ($_POST['postal_code'] ?? '')
        );

        /*
         * Validation
         */

        if ($receiverName === '') {
            Session::flash(
                'error',
                'نام گیرنده را وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if (mb_strlen($receiverName) < 2) {
            Session::flash(
                'error',
                'نام گیرنده باید حداقل ۲ کاراکتر باشد.'
            );

            $this->redirect('/addresses');
        }

        if ($phone === '') {
            Session::flash(
                'error',
                'شماره تماس را وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if (
            !preg_match(
                '/^09\d{9}$/',
                $phone
            )
        ) {
            Session::flash(
                'error',
                'شماره تماس معتبر وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if ($province === '') {
            Session::flash(
                'error',
                'استان را وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if ($city === '') {
            Session::flash(
                'error',
                'شهر را وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if ($address === '') {
            Session::flash(
                'error',
                'آدرس کامل را وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if (mb_strlen($address) < 10) {
            Session::flash(
                'error',
                'آدرس کامل‌تری وارد کنید.'
            );

            $this->redirect('/addresses');
        }

        if (
            $postalCode !== ''
            && !preg_match(
                '/^\d{10}$/',
                $postalCode
            )
        ) {
            Session::flash(
                'error',
                'کد پستی باید ۱۰ رقم باشد.'
            );

            $this->redirect('/addresses');
        }

        /*
         * Create address
         */

        $this->addresses->create(
            (int) $user['id'],
            [
                'title' => $title !== ''
                    ? $title
                    : null,

                'receiver_name' => $receiverName,

                'phone' => $phone,

                'province' => $province,

                'city' => $city,

                'address' => $address,

                'postal_code' => $postalCode !== ''
                    ? $postalCode
                    : null,
            ]
        );

        Session::flash(
            'success',
            'آدرس با موفقیت اضافه شد.'
        );

        $this->redirect('/addresses');
    }

    public function delete(): void
    {
        $user = $this->requireAuth();

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست نامعتبر است.'
            );

            $this->redirect('/addresses');
        }

        $addressId = (int) (
            $_POST['id'] ?? 0
        );

        if ($addressId <= 0) {
            Session::flash(
                'error',
                'شناسه آدرس نامعتبر است.'
            );

            $this->redirect('/addresses');
        }

        $deleted = $this->addresses->delete(
            $addressId,
            (int) $user['id']
        );

        if (!$deleted) {
            Session::flash(
                'error',
                'آدرس موردنظر پیدا نشد.'
            );

            $this->redirect('/addresses');
        }

        Session::flash(
            'success',
            'آدرس حذف شد.'
        );

        $this->redirect('/addresses');
    }

    private function requireAuth(): array
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای مدیریت آدرس‌ها ابتدا وارد حساب کاربری شوید.'
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
}