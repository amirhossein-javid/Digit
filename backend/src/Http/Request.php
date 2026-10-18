<?php

declare(strict_types=1);

namespace Nova\Http;

final class Request
{
    public static function requireGet(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            header('Allow: GET');
            ApiResponse::error('method_not_allowed', 'این مسیر فقط درخواست GET را می‌پذیرد.', 405);
        }
    }

    public static function optionalString(string $key, int $maxLength): ?string
    {
        if (!isset($_GET[$key]) || $_GET[$key] === '') {
            return null;
        }

        if (!is_string($_GET[$key])) {
            ApiResponse::error('invalid_parameter', sprintf('پارامتر «%s» نامعتبر است.', $key), 422);
        }

        $value = trim($_GET[$key]);

        if ($value === '' || mb_strlen($value, 'UTF-8') > $maxLength) {
            ApiResponse::error('invalid_parameter', sprintf('پارامتر «%s» نامعتبر است.', $key), 422);
        }

        return $value;
    }

    public static function positiveInt(string $key, bool $required = false): ?int
    {
        if (!isset($_GET[$key]) || $_GET[$key] === '') {
            if ($required) {
                ApiResponse::error('missing_parameter', sprintf('پارامتر «%s» الزامی است.', $key), 422);
            }

            return null;
        }

        $value = filter_var($_GET[$key], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($value === false) {
            ApiResponse::error('invalid_parameter', sprintf('پارامتر «%s» باید عددی مثبت باشد.', $key), 422);
        }

        return $value;
    }

    /** @param list<string> $allowed */
    public static function enum(string $key, array $allowed, string $default): string
    {
        $value = $_GET[$key] ?? $default;

        if (!is_string($value) || !in_array($value, $allowed, true)) {
            ApiResponse::error('invalid_parameter', sprintf('مقدار پارامتر «%s» مجاز نیست.', $key), 422);
        }

        return $value;
    }

    public static function boundedInt(string $key, int $default, int $minimum, int $maximum): int
    {
        if (!isset($_GET[$key]) || $_GET[$key] === '') {
            return $default;
        }

        $value = filter_var($_GET[$key], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $minimum, 'max_range' => $maximum],
        ]);

        if ($value === false) {
            ApiResponse::error('invalid_parameter', sprintf('پارامتر «%s» خارج از محدوده مجاز است.', $key), 422);
        }

        return $value;
    }
}
