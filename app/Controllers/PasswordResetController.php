<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use App\Models\VerificationCode;
use App\Core\Mailer;

final class PasswordResetController extends Controller
{
    private User $users;

    private VerificationCode $codes;

    private Mailer $mailer;

    public function __construct()
    {
        $this->users = new User();

        $this->codes = new VerificationCode();

        $this->mailer = new Mailer();
    }

    public function showForgotPassword(): void
    {
        $this->view(
            'auth/forgot-password',
            [
                'title' => 'بازیابی رمز عبور',
                'csrfField' => Csrf::field(),
            ]
        );
    }

    public function sendCode(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست نامعتبر است.'
            );

            $this->redirect('/forgot-password');
        }

        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            Session::flash(
                'error',
                'ایمیل معتبر وارد کنید.'
            );

            $this->redirect('/forgot-password');
        }

        $user = $this->users->findByEmail($email);

        /*
         * برای جلوگیری از افشای وجود حساب،
         * برای ایمیل ناموجود نیز پیام عمومی نمایش داده می‌شود.
         */
        if ($user === null) {
            Session::flash(
                'success',
                'اگر این ایمیل در فروشگاه ثبت شده باشد، کد بازیابی ارسال خواهد شد.'
            );

            $this->redirect('/forgot-password');
        }

        $code = (string) random_int(
            100000,
            999999
        );

        $userId = (int) $user['id'];

        $this->codes->invalidatePrevious(
            $userId,
            'password_reset'
        );

        $this->codes->create(
            $userId,
            'password_reset',
            $code,
            10
        );

        $this->mailer->sendVerificationCode(
            $email,
            (string) $user['name'],
            $code
        );

        Session::put(
            'password_reset_user',
            $userId
        );

        Session::flash(
            'success',
            'کد بازیابی به ایمیل شما ارسال شد.'
        );

        $this->redirect('/verify-reset-code');
    }

    public function showVerifyCode(): void
    {
        $userId = Session::get(
            'password_reset_user'
        );

        if (!$userId) {
            $this->redirect('/forgot-password');
        }

        $this->view(
            'auth/verify-reset-code',
            [
                'title' => 'تایید کد بازیابی',
                'csrfField' => Csrf::field(),
            ]
        );
    }

    public function verifyCode(): void
    {
        $userId = Session::get(
            'password_reset_user'
        );

        if (!$userId) {
            $this->redirect('/forgot-password');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست نامعتبر است.'
            );

            $this->redirect('/verify-reset-code');
        }

        $code = trim(
            (string) ($_POST['code'] ?? '')
        );

        if (
            strlen($code) !== 6
            || !ctype_digit($code)
        ) {
            Session::flash(
                'error',
                'کد بازیابی نامعتبر است.'
            );

            $this->redirect('/verify-reset-code');
        }

        $latest = $this->codes->findLatestActive(
            (int) $userId,
            'password_reset'
        );

        if ($latest === null) {
            Session::flash(
                'error',
                'کد بازیابی منقضی شده است.'
            );

            $this->redirect('/verify-reset-code');
        }

        if (
            !$this->codes->verify(
                (int) $latest['id'],
                $code
            )
        ) {
            Session::flash(
                'error',
                'کد بازیابی اشتباه است.'
            );

            $this->redirect('/verify-reset-code');
        }

        /*
         * کد صحیح است.
         * فعلاً رمز را تغییر نمی‌دهیم.
         * یک Session موقت برای مرحله تعیین رمز ایجاد می‌کنیم.
         */
        Session::put(
            'password_reset_verified_user',
            (int) $userId
        );

        Session::forget(
            'password_reset_user'
        );

        Session::flash(
            'success',
            'کد بازیابی با موفقیت تایید شد.'
        );

        $this->redirect('/reset-password');
    }

    public function showResetPassword(): void
    {
        $userId = Session::get(
            'password_reset_verified_user'
        );

        if (!$userId) {
            $this->redirect('/forgot-password');
        }

        $this->view(
            'auth/reset-password',
            [
                'title' => 'تعیین رمز عبور جدید',
                'csrfField' => Csrf::field(),
            ]
        );
    }

    public function resetPassword(): void
    {
        $userId = Session::get(
            'password_reset_verified_user'
        );

        if (!$userId) {
            $this->redirect('/forgot-password');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست نامعتبر است.'
            );

            $this->redirect('/reset-password');
        }

        $password = (string) (
            $_POST['password'] ?? ''
        );

        $passwordConfirmation = (string) (
            $_POST['password_confirmation'] ?? ''
        );

        if (strlen($password) < 8) {
            Session::flash(
                'error',
                'رمز عبور باید حداقل ۸ کاراکتر باشد.'
            );

            $this->redirect('/reset-password');
        }

        if ($password !== $passwordConfirmation) {
            Session::flash(
                'error',
                'تکرار رمز عبور با رمز عبور یکسان نیست.'
            );

            $this->redirect('/reset-password');
        }

        if (
            !$this->users->updatePassword(
                (int) $userId,
                $password
            )
        ) {
            Session::flash(
                'error',
                'تغییر رمز عبور انجام نشد.'
            );

            $this->redirect('/reset-password');
        }

        Session::forget(
            'password_reset_verified_user'
        );

        Session::flash(
            'success',
            'رمز عبور شما با موفقیت تغییر کرد.'
        );

        $this->redirect('/login');
    }
}
