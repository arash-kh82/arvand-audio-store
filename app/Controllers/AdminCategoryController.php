<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;

final class AdminCategoryController extends Controller
{
    private Category $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    /**
     * Ù„ÛŒØ³Øª Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒâ€ŒÙ‡Ø§
     */
    public function index(): void
    {
        $search = trim(
            (string) ($_GET['search'] ?? '')
        );

        $status = trim(
            (string) ($_GET['status'] ?? '')
        );

        $categories = $this->category->getAdminCategories(
            $search,
            $status
        );

        $this->view(
            'admin/categories/index',
            [
                'categories' => $categories,
                'search' => $search,
                'status' => $status,
            ]
        );
    }

    /**
     * ÙØ±Ù… Ø§ÛŒØ¬Ø§Ø¯ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ
     */
    public function create(): void
    {
        $categories = $this->category->getAdminCategories();

        $this->view(
            'admin/categories/create',
            [
                'categories' => $categories,
            ]
        );
    }

    /**
     * Ø§ÛŒØ¬Ø§Ø¯ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ
     */
    public function store(): void
    {
        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $slug = trim(
            (string) ($_POST['slug'] ?? '')
        );

        $description = trim(
            (string) ($_POST['description'] ?? '')
        );

        $image = trim(
            (string) ($_POST['image'] ?? '')
        );

        $parentId = (int) (
            $_POST['parent_id'] ?? 0
        );

        $status = isset($_POST['status'])
            ? 1
            : 0;

        if ($name === '' || $slug === '') {
            $this->redirect(
                '/admin/categories/create'
            );

            return;
        }

        if ($this->category->slugExists($slug)) {
            $this->redirect(
                '/admin/categories/create'
            );

            return;
        }

        $this->category->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'image' => $image,
            'parent_id' => $parentId > 0
                ? $parentId
                : null,
            'status' => $status,
        ]);

        $this->redirect(
            '/admin/categories'
        );
    }

    /**
     * ÙØ±Ù… ÙˆÛŒØ±Ø§ÛŒØ´ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ
     */
    public function edit(int $id): void
    {
        $category = $this->category->findAdminById($id);

        if ($category === null) {
            $this->redirect(
                '/admin/categories'
            );

            return;
        }

        $categories = $this->category->getAdminCategories();

        $this->view(
            'admin/categories/edit',
            [
                'category' => $category,
                'categories' => $categories,
            ]
        );
    }

    /**
     * Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ
     */
    public function update(int $id): void
    {
        $category = $this->category->findAdminById($id);

        if ($category === null) {
            $this->redirect(
                '/admin/categories'
            );

            return;
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $slug = trim(
            (string) ($_POST['slug'] ?? '')
        );

        $description = trim(
            (string) ($_POST['description'] ?? '')
        );

        $image = trim(
            (string) ($_POST['image'] ?? '')
        );

        $parentId = (int) (
            $_POST['parent_id'] ?? 0
        );

        $status = isset($_POST['status'])
            ? 1
            : 0;

        if ($name === '' || $slug === '') {
            $this->redirect(
                '/admin/categories/' . $id . '/edit'
            );

            return;
        }

        if (
            $this->category->slugExists(
                $slug,
                $id
            )
        ) {
            $this->redirect(
                '/admin/categories/' . $id . '/edit'
            );

            return;
        }

        /*
         * Ø¬Ù„ÙˆÚ¯ÛŒØ±ÛŒ Ø§Ø² Ù‚Ø±Ø§Ø± Ø¯Ø§Ø¯Ù† Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ Ø¨Ù‡ Ø¹Ù†ÙˆØ§Ù†
         * parent Ø®ÙˆØ¯Ø´
         */
        if ($parentId === $id) {
            $parentId = 0;
        }

        $this->category->update(
            $id,
            [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'image' => $image,
                'parent_id' => $parentId > 0
                    ? $parentId
                    : null,
                'status' => $status,
            ]
        );

        $this->redirect(
            '/admin/categories'
        );
    }

    /**
     * ØªØºÛŒÛŒØ± ÙˆØ¶Ø¹ÛŒØª Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ
     */
    public function toggleStatus(int $id): void
    {
        $category = $this->category->findAdminById($id);

        if ($category === null) {
            $this->redirect(
                '/admin/categories'
            );

            return;
        }

        $newStatus = (int) $category['status'] === 1
            ? 0
            : 1;

        $this->category->updateStatus(
            $id,
            $newStatus
        );

        $this->redirect(
            '/admin/categories'
        );
    }

    /**
     * Ø­Ø°Ù Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ
     */
    public function delete(int $id): void
    {
        if ($this->category->hasProducts($id)) {
            $this->redirect(
                '/admin/categories'
            );

            return;
        }

        $this->category->delete($id);

        $this->redirect(
            '/admin/categories'
        );
    }
}
