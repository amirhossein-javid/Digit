<?php

declare(strict_types=1);

namespace Nova\Http;

final class ApiResponse
{
    /** @param array<string, mixed> $meta */
    public static function success(mixed $data, array $meta = [], int $status = 200): never
    {
        $payload = ['success' => true, 'data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        self::send($payload, $status);
    }

    public static function error(string $code, string $message, int $status): never
    {
        self::send([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }

    /** @param array<string, mixed> $payload */
    private static function send(array $payload, int $status): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            http_response_code(500);
            $json = '{"success":false,"error":{"code":"encoding_error","message":"خطایی در پردازش پاسخ رخ داد."}}';
        }

        echo $json;
        exit;
    }
}
