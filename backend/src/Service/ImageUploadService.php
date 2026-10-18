<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Config\Environment;
use Nova\Exception\AdminValidationException;

final class ImageUploadService
{
    private const MAX_BYTES = 4_194_304;

    /** @param array<string, mixed>|null $file */
    public function save(?array $file, string $expectedMime, string $extension): ?string
    {
        if ($file === null || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !isset($file['tmp_name'], $file['size'])
            || !is_string($file['tmp_name'])
            || !is_uploaded_file($file['tmp_name'])) {
            throw new AdminValidationException(['بارگذاری تصویر کامل نشد.']);
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > self::MAX_BYTES) {
            throw new AdminValidationException(['حجم هر تصویر باید حداکثر ۴ مگابایت باشد.']);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($file['tmp_name']) !== $expectedMime) {
            throw new AdminValidationException(['فرمت واقعی تصویر با فرمت درخواستی مطابقت ندارد.']);
        }
        $imageInfo = @getimagesize($file['tmp_name']);
        $expectedType = $extension === 'webp' ? IMAGETYPE_WEBP : IMAGETYPE_AVIF;
        if ($imageInfo === false
            || ($imageInfo[2] ?? null) !== $expectedType
            || (int) $imageInfo[0] < 100
            || (int) $imageInfo[1] < 100
            || (int) $imageInfo[0] > 8000
            || (int) $imageInfo[1] > 8000) {
            throw new AdminValidationException(['ساختار یا ابعاد تصویر معتبر نیست.']);
        }

        $configuredRoot = Environment::get('PUBLIC_ROOT');
        $publicRoot = is_string($configuredRoot) && trim($configuredRoot) !== ''
            ? trim($configuredRoot)
            : dirname(__DIR__, 3);
        $directory = rtrim($publicRoot, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR
            . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Product upload directory could not be created.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $target = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new \RuntimeException('Uploaded product image could not be stored.');
        }

        return 'assets/images/products/uploads/' . $filename;
    }

    public function remove(string $relativePath): void
    {
        if (!str_starts_with($relativePath, 'assets/images/products/uploads/')) {
            return;
        }
        $configuredRoot = Environment::get('PUBLIC_ROOT');
        $publicRoot = is_string($configuredRoot) && trim($configuredRoot) !== ''
            ? trim($configuredRoot)
            : dirname(__DIR__, 3);
        $target = rtrim($publicRoot, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($target)) {
            @unlink($target);
        }
    }
}
