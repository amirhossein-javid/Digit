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

    public static function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            ApiResponse::error('method_not_allowed', 'این مسیر فقط درخواست POST را می‌پذیرد.', 405);
        }
    }

    public static function requireSameOrigin(): void
    {
        $fetchSite = strtolower(self::header('Sec-Fetch-Site') ?? '');
        if ($fetchSite === 'cross-site') {
            ApiResponse::error('cross_site_request', 'درخواست از مبدأ دیگری پذیرفته نمی‌شود.', 403);
        }

        $origin = self::header('Origin');
        if ($origin === null) {
            return;
        }
        $originHost = parse_url($origin, PHP_URL_HOST);
        $requestHost = strtolower(trim(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0]));
        if (!is_string($originHost) || $requestHost === '' || strtolower($originHost) !== $requestHost) {
            ApiResponse::error('cross_site_request', 'درخواست از مبدأ دیگری پذیرفته نمی‌شود.', 403);
        }
    }

    /** @return array<string, mixed> */
    public static function jsonBody(int $maximumBytes = 32768): array
    {
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType !== 'application/json') {
            ApiResponse::error('unsupported_media_type', 'بدنه درخواست باید JSON باشد.', 415);
        }

        $contentLength = filter_var($_SERVER['CONTENT_LENGTH'] ?? null, FILTER_VALIDATE_INT);
        if ($contentLength !== false && $contentLength > $maximumBytes) {
            ApiResponse::error('payload_too_large', 'حجم درخواست بیش از حد مجاز است.', 413);
        }

        $raw = file_get_contents('php://input', false, null, 0, $maximumBytes + 1);
        if ($raw === false || $raw === '' || strlen($raw) > $maximumBytes) {
            ApiResponse::error('invalid_json', 'بدنه درخواست خالی یا بیش از حد مجاز است.', 400);
        }

        try {
            $payload = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            ApiResponse::error('invalid_json', 'ساختار JSON معتبر نیست.', 400);
        }

        if (!is_array($payload) || array_is_list($payload)) {
            ApiResponse::error('invalid_json', 'بدنه درخواست باید یک شیء JSON باشد.', 400);
        }

        return $payload;
    }

    public static function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $value = $_SERVER[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
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
