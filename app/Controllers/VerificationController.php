<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;
use App\Models\VerificationCode;

final class VerificationController extends Controller
{
    private VerificationCode $codes;

    private User $users;


    public function __construct()
    {
        $this->codes = new VerificationCode();

        $this->users = new User();
    }


    public function show(): void
    {
        $userId = Session::get(
            'pending_verification_user'
        );


        if (!$userId) {
            $this->redirect('/register');
        }


        $this->view(
            'auth/verify-email',
            [
                'title' => 'تایید ایمیل',
                'csrfField' => Csrf::field(),
            ]
        );
    }


    public function verify(): void
    {
        $userId = Session::get(
            'pending_verification_user'
        );


        if (!$userId) {
            $this->redirect('/register');
        }


        if (
            !Csrf::validate(
                $_POST['_token'] ?? null
            )
        ) {
            Session::flash(
                'error',
                'درخواست نامعتبر است.'
            );

            $this->redirect('/verify-email');
        }


        $code = trim(
            (string) (
                $_POST['code'] ?? ''
            )
        );


        if (
            strlen($code) !== 6
            || !ctype_digit($code)
        ) {
            Session::flash(
                'error',
                'کد تایید نامعتبر است.'
            );

            $this->redirect('/verify-email');
        }


        $latest = $this->codes->findLatestActive(
            (int) $userId,
            'email_verification'
        );


        if ($latest === null) {
            Session::flash(
                'error',
                'کد تایید منقضی شده است.'
            );

            $this->redirect('/verify-email');
        }


        if (
            !$this->codes->verify(
                (int) $latest['id'],
                $code
            )
        ) {
            Session::flash(
                'error',
                'کد تایید اشتباه است.'
            );

            $this->redirect('/verify-email');
        }


        $this->users->markEmailVerified(
            (int) $userId
        );


        $user = $this->users->findById(
            (int) $userId
        );


        if ($user === null) {
            $this->redirect('/register');
        }


        Session::regenerate(true);


        Auth::login(
            $this->users->publicUser($user)
        );


        Session::forget(
            'pending_verification_user'
        );


        Session::flash(
            'success',
            'ایمیل شما تایید شد.'
        );


        $this->redirect('/account');
    }
}