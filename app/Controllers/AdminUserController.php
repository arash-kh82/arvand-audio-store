<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\AdminUser;

final class AdminUserController extends AdminController
{
    private AdminUser $users;

    public function __construct()
    {
        $this->users = new AdminUser();
    }

    /**
     * لیست کاربران
     */
    public function index(): void
    {
        $this->requireAdmin();

        $search = trim(
            (string) ($_GET['search'] ?? '')
        );

        $role = trim(
            (string) ($_GET['role'] ?? '')
        );

        $status = trim(
            (string) ($_GET['status'] ?? '')
        );

        $page = max(
            1,
            (int) ($_GET['page'] ?? 1)
        );

        $perPage = 10;

        if (
            !in_array(
                $role,
                ['', 'customer', 'admin'],
                true
            )
        ) {
            $role = '';
        }

        if (
            !in_array(
                $status,
                ['', 'active', 'inactive'],
                true
            )
        ) {
            $status = '';
        }

        $total = $this->users->countUsers(
            $search,
            $role,
            $status
        );

        $totalPages = max(
            1,
            (int) ceil($total / $perPage)
        );

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $users = $this->users->getUsers(
            $search,
            $role,
            $status,
            $page,
            $perPage
        );

        $this->view(
            'admin/users/index',
            [
                'title' => 'مدیریت کاربران',

                'users' => $users,

                'statistics' =>
                    $this->users->getStatistics(),

                'search' => $search,

                'role' => $role,

                'status' => $status,

                'page' => $page,

                'perPage' => $perPage,

                'total' => $total,

                'totalPages' => $totalPages,

                'csrfField' => Csrf::field(),
            ]
        );
    }

    /**
     * نمایش فرم ویرایش
     */
    public function edit(string $id): void
    {
        $this->requireAdmin();

        $userId = (int) $id;

        if ($userId <= 0) {
            $this->notFound();
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->notFound();
        }

        $currentAdmin = $this->requireAdmin();

        $this->view(
            'admin/users/edit',
            [
                'title' => 'ویرایش کاربر',

                'user' => $user,

                'currentAdminId' =>
                    (int) $currentAdmin['id'],

                'csrfField' => Csrf::field(),
            ]
        );
    }

    /**
     * ذخیره ویرایش کاربر
     */
    public function update(string $id): void
    {
        $currentAdmin = $this->requireAdmin();

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        $userId = (int) $id;

        if ($userId <= 0) {
            $this->notFound();
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->notFound();
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        $errors = [];

        if (
            $name === ''
            || mb_strlen($name) < 2
            || mb_strlen($name) > 120
        ) {
            $errors[] =
                'نام باید بین ۲ تا ۱۲۰ کاراکتر باشد.';
        }

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $errors[] =
                'ایمیل وارد شده معتبر نیست.';
        } elseif (
            $this->users->emailExists(
                $email,
                $userId
            )
        ) {
            $errors[] =
                'این ایمیل قبلاً توسط کاربر دیگری استفاده شده است.';
        }

        if ($errors !== []) {
            $this->view(
                'admin/users/edit',
                [
                    'title' => 'ویرایش کاربر',

                    'user' => array_merge(
                        $user,
                        [
                            'name' => $name,
                            'email' => $email,
                        ]
                    ),

                    'currentAdminId' =>
                        (int) $currentAdmin['id'],

                    'errors' => $errors,

                    'csrfField' => Csrf::field(),
                ]
            );

            return;
        }

        if (
            !$this->users->updateUser(
                $userId,
                $name,
                $email
            )
        ) {
            throw new \RuntimeException(
                'اطلاعات کاربر به‌روزرسانی نشد.'
            );
        }

        Session::flash(
            'success',
            'اطلاعات کاربر با موفقیت به‌روزرسانی شد.'
        );

        $this->redirect(
            '/admin/users'
        );
    }

    /**
     * تغییر نقش کاربر
     */
    public function updateRole(string $id): void
    {
        $currentAdmin = $this->requireAdmin();

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        $userId = (int) $id;

        if ($userId <= 0) {
            $this->notFound();
        }

        $role = trim(
            (string) ($_POST['role'] ?? '')
        );

        if (
            !in_array(
                $role,
                ['customer', 'admin'],
                true
            )
        ) {
            Session::flash(
                'error',
                'نقش انتخاب‌شده معتبر نیست.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        if (
            $userId === (int) $currentAdmin['id']
            && $role !== 'admin'
        ) {
            Session::flash(
                'error',
                'نمی‌توانید نقش ادمین فعلی خودتان را تغییر دهید.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->notFound();
        }

        if (
            !$this->users->updateRole(
                $userId,
                $role
            )
        ) {
            throw new \RuntimeException(
                'نقش کاربر تغییر نکرد.'
            );
        }

        Session::flash(
            'success',
            'نقش کاربر با موفقیت تغییر کرد.'
        );

        $this->redirect(
            '/admin/users'
        );
    }

    /**
     * تغییر وضعیت کاربر
     */
    public function updateStatus(string $id): void
    {
        $currentAdmin = $this->requireAdmin();

        if (!$this->verifyCsrf()) {
            Session::flash(
                'error',
                'اعتبارسنجی امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        $userId = (int) $id;

        if ($userId <= 0) {
            $this->notFound();
        }

        $status = trim(
            (string) ($_POST['status'] ?? '')
        );

        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            Session::flash(
                'error',
                'وضعیت انتخاب‌شده معتبر نیست.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        if (
            $userId === (int) $currentAdmin['id']
            && $status !== 'active'
        ) {
            Session::flash(
                'error',
                'نمی‌توانید حساب ادمین فعلی خودتان را غیرفعال کنید.'
            );

            $this->redirect(
                '/admin/users'
            );
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->notFound();
        }

        if (
            !$this->users->updateStatus(
                $userId,
                $status
            )
        ) {
            throw new \RuntimeException(
                'وضعیت کاربر تغییر نکرد.'
            );
        }

        Session::flash(
            'success',
            'وضعیت کاربر با موفقیت تغییر کرد.'
        );

        $this->redirect(
            '/admin/users'
        );
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

        echo '404 - کاربر مورد نظر پیدا نشد.';
        exit;
    }
}