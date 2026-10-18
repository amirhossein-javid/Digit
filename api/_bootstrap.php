<?php

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$bootstrapCandidates = [
    dirname(__DIR__, 2) . '/nova-private/bootstrap.php',
    dirname(__DIR__) . '/backend/bootstrap.php',
];

foreach ($bootstrapCandidates as $bootstrapFile) {
    if (is_file($bootstrapFile)) {
        require $bootstrapFile;
        return;
    }
}

http_response_code(500);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo json_encode([
    'success' => false,
    'error' => [
        'code' => 'configuration_error',
        'message' => 'تنظیمات سرویس در دسترس نیست.',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
