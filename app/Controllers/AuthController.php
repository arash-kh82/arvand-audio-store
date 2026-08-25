<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use App\Models\Order;
use App\Models\VerificationCode;
use App\Core\Mailer;



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
                ['csrf' => 'اعتبارسنجی امنیتی نامعتبر است.'],
                $this->oldLoginData()
            );
            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $old = [
            'email' => $email,
        ];

        $errors = [];

        if (
            $email === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $errors['email'] = 'ایمیل معتبر وارد کنید.';
        }

        if ($password === '') {
            $errors['password'] = 'رمز عبور را وارد کنید.';
        }

        if ($errors !== []) {
            $this->renderLogin($errors, $old);
            return;
        }

        $user = $this->users->findByEmail($email);

        if (
            $user === null
            || !password_verify(
                $password,
                (string) $user['password']
            )
        ) {
            $this->renderLogin(
                ['credentials' => 'ایمیل یا رمز عبور نادرست است.'],
                $old
            );
            return;
        }

        if (($user['status'] ?? 'active') !== 'active') {
            $this->renderLogin(
                ['status' => 'حساب کاربری شما فعال نیست.'],
                $old
            );
            return;
        }
        
        // ====== بررسی تأیید ایمیل ======
        if ($user['email_verified_at'] === null) {
            Session::put(
                'pending_verification_user',
                $user['id']
            );
            
            $this->redirect('/verify-email');
            return;
        }

        $code = $this->generateVerificationCode();
        
        $this->verificationCodes->invalidatePrevious(
            $id,
            'email_verification'
        );
        
        $this->verificationCodes->create(
            $id,
            'email_verification',
            $code,
            10
        );
        
        
        Mailer::sendVerificationCode(
            $email,
            $code
        );
        
        
        Session::put(
            'pending_verification_user',
            $id
        );
        
        
        Session::flash(
            'success',
            'کد تایید به ایمیل شما ارسال شد.'
        );
        
        
        $this->redirect('/verify-email');
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect('/account');
        }

        if (!$this->verifyCsrf()) {
            $this->renderRegister(
                ['csrf' => 'اعتبارسنجی امنیتی نامعتبر است.'],
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

        if (
            $name === ''
            || mb_strlen($name) < 2
        ) {
            $errors['name'] =
                'نام باید حداقل ۲ کاراکتر باشد.';
        }

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
            $this->users->findByEmail($email) !== null
        ) {
            $errors['email'] =
                'این ایمیل قبلا ثبت شده است.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] =
                'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }

        if (
            $password !== $passwordConfirmation
        ) {
            $errors['password_confirmation'] =
                'تکرار رمز عبور با رمز عبور یکسان نیست.';
        }

        if ($errors !== []) {
            $this->renderRegister($errors, $old);
            return;
        }

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

        $user = $this->users->findById($id);

        if ($user === null) {
            throw new \RuntimeException(
                'کاربر ایجاد شد اما بازیابی آن ممکن نشد.'
            );
        }

        $code = (string) random_int(100000, 999999);

        $this->verificationCodes->invalidatePrevious(
            $id,
            'email_verification'
        );

        $this->verificationCodes->create(
            $id,
            'email_verification',
            $code
        );

        $this->mailer->sendVerificationCode(
            $email,
            $name,
            $code
        );

        Session::put(
            'pending_verification_user',
            $id
        );

        $this->redirect('/verify-email');

    }

    public function account(): void
    {
        if (!Auth::check()) {
            Session::flash(
                'error',
                'برای مشاهده حساب کاربری وارد شوید.'
            );

            $this->redirect('/login');
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $this->view('auth/account', [
            'title' => 'حساب کاربری',
            'user' => $user,
            'orders' => $this->orders->getUserOrders(
                (int) $user['id']
            ),
            'csrfField' => Csrf::field(),
        ]);
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/account');
        }

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect('/account');
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
        $this->view('auth/login', [
            'title' => 'ورود به حساب کاربری',
            'errors' => $errors,
            'old' => $old,
            'csrfField' => Csrf::field(),
        ]);
    }

    private function renderRegister(
        array $errors = [],
        array $old = []
    ): void {
        $this->view('auth/register', [
            'title' => 'ثبت‌نام',
            'errors' => $errors,
            'old' => $old,
            'csrfField' => Csrf::field(),
        ]);
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
        return (string) random_int(100000, 999999);
    }
}