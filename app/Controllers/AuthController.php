<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\Session;
use App\Models\Order;
use App\Models\User;
use App\Models\VerificationCode;

final class AuthController extends Controller
{
    private User $users;

    private Order $orders;

    private VerificationCode $verificationCodes;

    private Mailer $mailer;

    public function __construct()
    {
        $this->users = new User();

        $this->orders = new Order();

        $this->verificationCodes = new VerificationCode();

        $this->mailer = new Mailer();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/account');
        }

        $this->renderLogin();
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/account');
        }

        $this->renderRegister();
    }

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('/account');
        }

        if (!$this->verifyCsrf()) {
            $this->renderLogin(
                [
                    'csrf' => 'اعتبارسنجی امنیتی نامعتبر است.',
                ],
                $this->oldLoginData()
            );

            return;
        }

        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        $password = (string) (
            $_POST['password'] ?? ''
        );

        $old = [
            'email' => $email,
        ];

        $errors = [];

        /*
        |--------------------------------------------------------------------------
        | Validate email
        |--------------------------------------------------------------------------
        */

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $errors['email'] =
                'ایمیل معتبر وارد کنید.';
        }

        /*
        |--------------------------------------------------------------------------
        | Validate password
        |--------------------------------------------------------------------------
        */

        if ($password === '') {
            $errors['password'] =
                'رمز عبور را وارد کنید.';
        }

        if ($errors !== []) {
            $this->renderLogin(
                $errors,
                $old
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find user
        |--------------------------------------------------------------------------
        */

        $user = $this->users->findByEmail(
            $email
        );

        /*
        |--------------------------------------------------------------------------
        | Verify credentials
        |--------------------------------------------------------------------------
        */

        if (
            $user === null
            || !password_verify(
                $password,
                (string) $user['password']
            )
        ) {
            $this->renderLogin(
                [
                    'credentials' =>
                        'ایمیل یا رمز عبور نادرست است.',
                ],
                $old
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Check account status
        |--------------------------------------------------------------------------
        */

        if (
            ($user['status'] ?? 'active')
            !== 'active'
        ) {
            $this->renderLogin(
                [
                    'status' =>
                        'حساب کاربری شما فعال نیست.',
                ],
                $old
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Check email verification
        |--------------------------------------------------------------------------
        */

        if (
            ($user['email_verified_at'] ?? null)
            === null
        ) {
            Session::put(
                'pending_verification_user',
                (int) $user['id']
            );

            Session::flash(
                'error',
                'ابتدا ایمیل خود را تأیید کنید.'
            );

            $this->redirect(
                '/verify-email'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Session::regenerate(true);

        Auth::login(
            $this->users->publicUser($user)
        );

        Session::flash(
            'success',
            'با موفقیت وارد حساب کاربری شدید.'
        );

        $this->redirect(
            '/account'
        );
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect('/account');
        }

        if (!$this->verifyCsrf()) {
            $this->renderRegister(
                [
                    'csrf' =>
                        'اعتبارسنجی امنیتی نامعتبر است.',
                ],
                $this->oldRegisterData()
            );

            return;
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        $password = (string) (
            $_POST['password'] ?? ''
        );

        $passwordConfirmation = (string) (
            $_POST['password_confirmation'] ?? ''
        );

        $old = [
            'name' => $name,
            'email' => $email,
        ];

        $errors = [];

        /*
        |--------------------------------------------------------------------------
        | Validate name
        |--------------------------------------------------------------------------
        */

        if (
            $name === ''
            || mb_strlen($name) < 2
        ) {
            $errors['name'] =
                'نام باید حداقل ۲ کاراکتر باشد.';
        }

        /*
        |--------------------------------------------------------------------------
        | Validate email
        |--------------------------------------------------------------------------
        */

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $errors['email'] =
                'ایمیل معتبر وارد کنید.';
        } elseif (
            $this->users->findByEmail($email)
            !== null
        ) {
            $errors['email'] =
                'این ایمیل قبلاً ثبت شده است.';
        }

        /*
        |--------------------------------------------------------------------------
        | Validate password
        |--------------------------------------------------------------------------
        */

        if (mb_strlen($password) < 8) {
            $errors['password'] =
                'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }

        /*
        |--------------------------------------------------------------------------
        | Confirm password
        |--------------------------------------------------------------------------
        */

        if (
            $password
            !== $passwordConfirmation
        ) {
            $errors['password_confirmation'] =
                'تکرار رمز عبور با رمز عبور یکسان نیست.';
        }

        if ($errors !== []) {
            $this->renderRegister(
                $errors,
                $old
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create user
        |--------------------------------------------------------------------------
        */

        $id = $this->users->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            'role' => 'customer',
            'status' => 'active',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Retrieve created user
        |--------------------------------------------------------------------------
        */

        $user = $this->users->findById(
            $id
        );

        if ($user === null) {
            throw new \RuntimeException(
                'کاربر ایجاد شد اما بازیابی آن ممکن نشد.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate verification code
        |--------------------------------------------------------------------------
        */

        $code = $this->generateVerificationCode();

        $this->verificationCodes
            ->invalidatePrevious(
                $id,
                'email_verification'
            );

        $this->verificationCodes->create(
            $id,
            'email_verification',
            $code
        );

        /*
        |--------------------------------------------------------------------------
        | Send verification email
        |--------------------------------------------------------------------------
        */

        $this->mailer->sendVerificationCode(
            $email,
            $name,
            $code
        );

        /*
        |--------------------------------------------------------------------------
        | Store pending verification user
        |--------------------------------------------------------------------------
        */

        Session::put(
            'pending_verification_user',
            $id
        );

        Session::flash(
            'success',
            'کد تأیید به ایمیل شما ارسال شد.'
        );

        $this->redirect(
            '/verify-email'
        );
    }

    public function account(): void
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای مشاهده حساب کاربری وارد شوید.'
            );

            $this->redirect('/login');

            return;
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');

            return;
        }

        $this->view(
            'auth/account',
            [
                'title' => 'حساب کاربری',
                'user' => $user,
                'orders' => $this->orders
                    ->getUserOrders(
                        (int) $user['id']
                    ),
                'csrfField' => Csrf::field(),
            ]
        );
    }

    public function logout(): void
    {
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET')
            !== 'POST'
        ) {
            $this->redirect('/account');

            return;
        }

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/account');

            return;
        }

        Auth::logout();

        Session::destroy();

        Session::start();

        Session::flash(
            'success',
            'با موفقیت خارج شدید.'
        );

        $this->redirect('/login');
    }

    private function renderLogin(
        array $errors = [],
        array $old = []
    ): void {
        $this->view(
            'auth/login',
            [
                'title' => 'ورود به حساب کاربری',
                'errors' => $errors,
                'old' => $old,
                'csrfField' => Csrf::field(),
            ]
        );
    }

    private function renderRegister(
        array $errors = [],
        array $old = []
    ): void {
        $this->view(
            'auth/register',
            [
                'title' => 'ثبت‌نام',
                'errors' => $errors,
                'old' => $old,
                'csrfField' => Csrf::field(),
            ]
        );
    }

    private function verifyCsrf(): bool
    {
        return Csrf::validate(
            $_POST['_token'] ?? null
        );
    }

    private function oldLoginData(): array
    {
        return [
            'email' => trim(
                (string) ($_POST['email'] ?? '')
            ),
        ];
    }

    private function oldRegisterData(): array
    {
        return [
            'name' => trim(
                (string) ($_POST['name'] ?? '')
            ),
            'email' => trim(
                (string) ($_POST['email'] ?? '')
            ),
        ];
    }

    private function generateVerificationCode(): string
    {
        return (string) random_int(
            100000,
            999999
        );
    }
}