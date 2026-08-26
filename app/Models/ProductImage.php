<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;

final class ProductImage extends Model
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * مسیر فیزیکی ذخیره تصاویر
     */
    public static function uploadDirectory(): string
    {
        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . 'public'
            . DIRECTORY_SEPARATOR
            . 'assets'
            . DIRECTORY_SEPARATOR
            . 'images'
            . DIRECTORY_SEPARATOR
            . 'products';
    }

    /**
     * مسیر عمومی تصویر
     */
    public static function publicPath(string $filename): string
    {
        return '/assets/images/products/' . ltrim(
            $filename,
            '/'
        );
    }

    /**
     * دریافت تصاویر یک محصول
     */
    public function getByProductId(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT *
             FROM product_images
             WHERE product_id = :product_id
             ORDER BY sort_order ASC, id ASC'
        );

        $stmt->execute([
            ':product_id' => $productId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * دریافت یک تصویر
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT *
             FROM product_images
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $image = $stmt->fetch();

        return $image !== false
            ? $image
            : null;
    }

    /**
     * دریافت تصویر اصلی
     */
    public function getPrimary(int $productId): ?array
    {
        if ($productId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT *
             FROM product_images
             WHERE product_id = :product_id
             ORDER BY sort_order ASC, id ASC
             LIMIT 1'
        );

        $stmt->execute([
            ':product_id' => $productId,
        ]);

        $image = $stmt->fetch();

        return $image !== false
            ? $image
            : null;
    }

    /**
     * ذخیره تصویر جدید
     */
    public function create(
        int $productId,
        string $filename,
        string $altText = ''
    ): int {
        if ($productId <= 0 || $filename === '') {
            throw new RuntimeException(
                'اطلاعات تصویر نامعتبر است.'
            );
        }

        $stmt = $this->db->prepare(
            'SELECT COALESCE(MAX(sort_order), -1)
             FROM product_images
             WHERE product_id = :product_id'
        );

        $stmt->execute([
            ':product_id' => $productId,
        ]);

        $sortOrder = (int) $stmt->fetchColumn() + 1;

        $stmt = $this->db->prepare(
            'INSERT INTO product_images
                (
                    product_id,
                    image,
                    alt_text,
                    sort_order
                )
             VALUES
                (
                    :product_id,
                    :image,
                    :alt_text,
                    :sort_order
                )'
        );

        $stmt->execute([
            ':product_id' => $productId,
            ':image' => self::publicPath($filename),
            ':alt_text' => trim($altText) !== ''
                ? trim($altText)
                : null,
            ':sort_order' => $sortOrder,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * تغییر تصویر اصلی
     */
    public function setPrimary(int $imageId): bool
    {
        $image = $this->findById($imageId);

        if ($image === null) {
            return false;
        }

        $productId = (int) $image['product_id'];

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'UPDATE product_images
                 SET sort_order = sort_order + 1
                 WHERE product_id = :product_id'
            );

            $stmt->execute([
                ':product_id' => $productId,
            ]);

            $stmt = $this->db->prepare(
                'UPDATE product_images
                 SET sort_order = 0
                 WHERE id = :id'
            );

            $stmt->execute([
                ':id' => $imageId,
            ]);

            $stmt = $this->db->prepare(
                'UPDATE products
                 SET image = :image
                 WHERE id = :product_id'
            );

            $stmt->execute([
                ':image' => $image['image'],
                ':product_id' => $productId,
            ]);

            $this->db->commit();

            return true;

        } catch (\Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * حذف تصویر
     */
    public function delete(int $imageId): ?array
    {
        $image = $this->findById($imageId);

        if ($image === null) {
            return null;
        }

        $productId = (int) $image['product_id'];

        $wasPrimary = (int) $image['sort_order'] === 0;

        $stmt = $this->db->prepare(
            'DELETE FROM product_images
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $imageId,
        ]);

        if ($wasPrimary) {

            $nextPrimary = $this->getPrimary(
                $productId
            );

            if ($nextPrimary !== null) {
                $this->setPrimary(
                    (int) $nextPrimary['id']
                );
            } else {
                $stmt = $this->db->prepare(
                    'UPDATE products
                     SET image = NULL
                     WHERE id = :product_id'
                );

                $stmt->execute([
                    ':product_id' => $productId,
                ]);
            }
        }

        return $image;
    }

    /**
     * اعتبارسنجی فایل آپلودی
     */
    public function validateUpload(
        array $file
    ): string {
        if (
            !isset(
                $file['error'],
                $file['tmp_name'],
                $file['size']
            )
        ) {
            throw new RuntimeException(
                'فایل تصویر دریافت نشد.'
            );
        }

        if (
            (int) $file['error']
            !== UPLOAD_ERR_OK
        ) {
            throw new RuntimeException(
                'آپلود تصویر با خطا مواجه شد.'
            );
        }

        if (
            !is_uploaded_file(
                $file['tmp_name']
            )
        ) {
            throw new RuntimeException(
                'فایل آپلودشده معتبر نیست.'
            );
        }

        $size = (int) $file['size'];

        if ($size <= 0) {
            throw new RuntimeException(
                'فایل تصویر خالی است.'
            );
        }

        if ($size > self::MAX_FILE_SIZE) {
            throw new RuntimeException(
                'حجم تصویر نباید بیشتر از 5 مگابایت باشد.'
            );
        }

        $imageInfo = @getimagesize(
            $file['tmp_name']
        );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'فایل انتخاب‌شده یک تصویر معتبر نیست.'
            );
        }

        $finfo = new \finfo(
            FILEINFO_MIME_TYPE
        );

        $mimeType = $finfo->file(
            $file['tmp_name']
        );

        if (
            !isset(
                self::ALLOWED_MIME_TYPES[$mimeType]
            )
        ) {
            throw new RuntimeException(
                'فرمت تصویر مجاز نیست. فقط JPG، PNG و WebP مجاز هستند.'
            );
        }

        return self::ALLOWED_MIME_TYPES[
            $mimeType
        ];
    }

    /**
     * انتقال فایل به پوشه تصاویر
     */
    public function storeUploadedFile(
        array $file,
        string $extension
    ): string {
        $directory = self::uploadDirectory();

        if (
            !is_dir($directory)
            && !mkdir(
                $directory,
                0755,
                true
            )
        ) {
            throw new RuntimeException(
                'پوشه ذخیره تصاویر ایجاد نشد.'
            );
        }

        $filename =
            bin2hex(random_bytes(16))
            . '.'
            . $extension;

        $destination =
            $directory
            . DIRECTORY_SEPARATOR
            . $filename;

        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {
            throw new RuntimeException(
                'ذخیره تصویر انجام نشد.'
            );
        }

        return $filename;
    }

    /**
     * حذف فایل از دیسک
     */
    public function deleteFile(
        string $publicPath
    ): void {
        $prefix = '/assets/images/products/';

        if (
            !str_starts_with(
                $publicPath,
                $prefix
            )
        ) {
            return;
        }

        $filename = basename(
            $publicPath
        );

        $file =
            self::uploadDirectory()
            . DIRECTORY_SEPARATOR
            . $filename;

        if (is_file($file)) {
            @unlink($file);
        }
    }
}