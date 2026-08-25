<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Brand;

final class AdminBrandController extends Controller
{
    private Brand $brand;

    public function __construct()
    {
        $this->brand = new Brand();
    }

    /**
     * Ù„ÛŒØ³Øª Ø¨Ø±Ù†Ø¯Ù‡Ø§
     */
    public function index(): void
    {
        $search = trim(
            (string) ($_GET['search'] ?? '')
        );

        $status = trim(
            (string) ($_GET['status'] ?? '')
        );

        $brands = $this->brand->getAdminBrands(
            $search,
            $status
        );

        $this->view(
            'admin/brands/index',
            [
                'brands' => $brands,
                'search' => $search,
                'status' => $status,
            ]
        );
    }

    /**
     * ÙØ±Ù… Ø§ÛŒØ¬Ø§Ø¯ Ø¨Ø±Ù†Ø¯
     */
    public function create(): void
    {
        $this->view(
            'admin/brands/create'
        );
    }

    /**
     * Ø§ÛŒØ¬Ø§Ø¯ Ø¨Ø±Ù†Ø¯
     */
    public function store(): void
    {
        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $slug = trim(
            (string) ($_POST['slug'] ?? '')
        );

        $logo = trim(
            (string) ($_POST['logo'] ?? '')
        );

        $status = isset($_POST['status'])
            ? 1
            : 0;

        if ($name === '' || $slug === '') {
            $this->redirect(
                '/admin/brands/create'
            );

            return;
        }

        if ($this->brand->slugExists($slug)) {
            $this->redirect(
                '/admin/brands/create'
            );

            return;
        }

        $this->brand->create([
            'name' => $name,
            'slug' => $slug,
            'logo' => $logo,
            'status' => $status,
        ]);

        $this->redirect(
            '/admin/brands'
        );
    }

    /**
     * ÙØ±Ù… ÙˆÛŒØ±Ø§ÛŒØ´ Ø¨Ø±Ù†Ø¯
     */
    public function edit(int $id): void
    {
        $brand = $this->brand->findAdminById($id);

        if ($brand === null) {
            $this->redirect(
                '/admin/brands'
            );

            return;
        }

        $this->view(
            'admin/brands/edit',
            [
                'brand' => $brand,
            ]
        );
    }

    /**
     * Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ø¨Ø±Ù†Ø¯
     */
    public function update(int $id): void
    {
        $brand = $this->brand->findAdminById($id);

        if ($brand === null) {
            $this->redirect(
                '/admin/brands'
            );

            return;
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $slug = trim(
            (string) ($_POST['slug'] ?? '')
        );

        $logo = trim(
            (string) ($_POST['logo'] ?? '')
        );

        $status = isset($_POST['status'])
            ? 1
            : 0;

        if ($name === '' || $slug === '') {
            $this->redirect(
                '/admin/brands/' . $id . '/edit'
            );

            return;
        }

        if (
            $this->brand->slugExists(
                $slug,
                $id
            )
        ) {
            $this->redirect(
                '/admin/brands/' . $id . '/edit'
            );

            return;
        }

        $this->brand->update(
            $id,
            [
                'name' => $name,
                'slug' => $slug,
                'logo' => $logo,
                'status' => $status,
            ]
        );

        $this->redirect(
            '/admin/brands'
        );
    }

    /**
     * ØªØºÛŒÛŒØ± ÙˆØ¶Ø¹ÛŒØª Ø¨Ø±Ù†Ø¯
     */
    public function toggleStatus(int $id): void
    {
        $brand = $this->brand->findAdminById($id);

        if ($brand === null) {
            $this->redirect(
                '/admin/brands'
            );

            return;
        }

        $newStatus = (int) $brand['status'] === 1
            ? 0
            : 1;

        $this->brand->updateStatus(
            $id,
            $newStatus
        );

        $this->redirect(
            '/admin/brands'
        );
    }

    /**
     * Ø­Ø°Ù Ø¨Ø±Ù†Ø¯
     */
    public function delete(int $id): void
    {
        if ($this->brand->hasProducts($id)) {
            $this->redirect(
                '/admin/brands'
            );

            return;
        }

        $this->brand->delete($id);

        $this->redirect(
            '/admin/brands'
        );
    }
}
