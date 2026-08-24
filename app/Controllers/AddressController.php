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


        $this->addresses->create(
            (int) $user['id'],
            [
                'title' => trim($_POST['title'] ?? ''),
                'receiver_name' => trim($_POST['receiver_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'province' => trim($_POST['province'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'postal_code' => trim($_POST['postal_code'] ?? ''),
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


        $this->addresses->delete(
            (int) ($_POST['id'] ?? 0),
            (int) $user['id']
        );


        Session::flash(
            'success',
            'آدرس حذف شد.'
        );


        $this->redirect('/addresses');
    }


    private function requireAuth(): array
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        return Auth::user();
    }
}