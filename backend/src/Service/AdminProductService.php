<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Exception\AdminValidationException;
use Nova\Repository\AdminProductRepository;
use Throwable;

final class AdminProductService
{
    private const STOCK_STATUSES = ['inquiry', 'in_stock', 'out_of_stock'];

    public function __construct(
        private readonly AdminProductRepository $products,
        private readonly ImageUploadService $images
    ) {
    }

    /** @param array<string, mixed> $input
     *  @param array<string, array<string, mixed>> $files
     */
    public function save(?int $id, array $input, array $files): int
    {
        $current = $id === null ? null : $this->products->find($id);
        if ($id !== null && $current === null) {
            throw new AdminValidationException(['محصول موردنظر پیدا نشد.']);
        }

        $errors = [];
        $name = $this->text($input['name'] ?? null, 3, 190, 'نام محصول', $errors);
        $slug = strtolower(trim(is_string($input['slug'] ?? null) ? $input['slug'] : ''));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 190) {
            $errors[] = 'نامک باید فقط شامل حروف انگلیسی کوچک، عدد و خط تیره باشد.';
        } elseif ($this->products->slugExists($slug, $id)) {
            $errors[] = 'این نامک قبلاً استفاده شده است.';
        }

        $description = $this->text($input['description'] ?? null, 10, 5000, 'توضیحات', $errors);
        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($categoryId === false || !$this->products->categoryExists((int) $categoryId)) {
            $errors[] = 'دسته‌بندی انتخاب‌شده معتبر نیست.';
        }
        $price = filter_var($input['price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($price === false) {
            $errors[] = 'قیمت باید یک عدد صحیح مثبت باشد.';
        }
        $stockStatus = is_string($input['stock_status'] ?? null) ? $input['stock_status'] : '';
        if (!in_array($stockStatus, self::STOCK_STATUSES, true)) {
            $errors[] = 'وضعیت موجودی معتبر نیست.';
        }
        $specs = $this->specs($input['spec_key'] ?? [], $input['spec_value'] ?? [], $errors);

        if ($errors !== []) {
            throw new AdminValidationException($errors);
        }

        $newFiles = [];
        try {
            $webp = $this->images->save($files['image_webp'] ?? null, 'image/webp', 'webp');
            if ($webp !== null) {
                $newFiles[] = $webp;
            }
            $avif = $this->images->save($files['image_avif'] ?? null, 'image/avif', 'avif');
            if ($avif !== null) {
                $newFiles[] = $avif;
            }
            if ($current === null && ($webp === null || $avif === null)) {
                throw new AdminValidationException(['برای محصول جدید هر دو تصویر WebP و AVIF الزامی است.']);
            }

            return $this->products->save($id, [
                'category_id' => (int) $categoryId,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'price' => (int) $price,
                'image_webp' => $webp ?? (string) $current['image_webp'],
                'image_avif' => $avif ?? (string) $current['image_avif'],
                'is_featured' => isset($input['is_featured']) ? 1 : 0,
                'is_new' => isset($input['is_new']) ? 1 : 0,
                'stock_status' => $stockStatus,
            ], $specs);
        } catch (Throwable $exception) {
            foreach ($newFiles as $path) {
                $this->images->remove($path);
            }
            throw $exception;
        }
    }

    /** @param list<string> $errors */
    private function text(mixed $value, int $minimum, int $maximum, string $label, array &$errors): string
    {
        $text = is_string($value) ? trim($value) : '';
        $length = mb_strlen($text, 'UTF-8');
        if ($length < $minimum || $length > $maximum) {
            $errors[] = sprintf('فیلد «%s» معتبر نیست.', $label);
        }
        return $text;
    }

    /** @param mixed $keys
     *  @param mixed $values
     *  @param list<string> $errors
     *  @return list<array{key: string, value: string}>
     */
    private function specs(mixed $keys, mixed $values, array &$errors): array
    {
        if (!is_array($keys) || !is_array($values) || count($keys) !== count($values) || count($keys) > 20) {
            $errors[] = 'ساختار مشخصات فنی معتبر نیست.';
            return [];
        }
        $specs = [];
        foreach ($keys as $index => $key) {
            $key = is_string($key) ? trim($key) : '';
            $value = is_string($values[$index] ?? null) ? trim($values[$index]) : '';
            if ($key === '' && $value === '') {
                continue;
            }
            if ($key === '' || $value === '' || mb_strlen($key, 'UTF-8') > 100 || mb_strlen($value, 'UTF-8') > 255) {
                $errors[] = 'عنوان و مقدار هر مشخصه باید کامل و در محدوده مجاز باشد.';
                continue;
            }
            $specs[] = ['key' => $key, 'value' => $value];
        }
        return $specs;
    }
}
