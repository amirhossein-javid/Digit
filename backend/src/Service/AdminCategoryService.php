<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Exception\AdminValidationException;
use Nova\Repository\AdminCategoryRepository;

final class AdminCategoryService
{
    public function __construct(private readonly AdminCategoryRepository $categories)
    {
    }

    /** @param array<string, mixed> $input */
    public function save(?int $id, array $input): int
    {
        if ($id !== null && $this->categories->find($id) === null) {
            throw new AdminValidationException(['دسته‌بندی پیدا نشد.']);
        }
        $errors = [];
        $name = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        if (mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 100) {
            $errors[] = 'نام دسته‌بندی باید بین ۲ تا ۱۰۰ کاراکتر باشد.';
        } elseif ($this->categories->valueExists('name', $name, $id)) {
            $errors[] = 'این نام دسته‌بندی قبلاً ثبت شده است.';
        }
        $slug = strtolower(trim(is_string($input['slug'] ?? null) ? $input['slug'] : ''));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 100) {
            $errors[] = 'نامک دسته‌بندی معتبر نیست.';
        } elseif ($this->categories->valueExists('slug', $slug, $id)) {
            $errors[] = 'این نامک قبلاً ثبت شده است.';
        }
        $position = filter_var($input['position'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 65535],
        ]);
        if ($position === false) {
            $errors[] = 'ترتیب نمایش باید عددی بین صفر تا ۶۵۵۳۵ باشد.';
        }
        if ($errors !== []) {
            throw new AdminValidationException($errors);
        }

        return $this->categories->save($id, $name, $slug, (int) $position);
    }
}
