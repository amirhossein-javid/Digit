<?php

declare(strict_types=1);

use Nova\Config\Environment;
use Nova\Database\Connection;
use Nova\Http\ApiResponse;

spl_autoload_register(static function (string $class): void {
    $prefix = 'Nova\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception): void {
    error_log(sprintf(
        '[Nova API] %s in %s:%d',
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    ApiResponse::error('internal_error', 'خطایی در پردازش درخواست رخ داد.', 500);
});

register_shutdown_function(static function (): void {
    $error = error_get_last();

    if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    error_log(sprintf('[Nova API] Fatal error in %s:%d', $error['file'], $error['line']));

    if (!headers_sent()) {
        ApiResponse::error('internal_error', 'خطایی در پردازش درخواست رخ داد.', 500);
    }
});

$environmentFiles = array_filter([
    getenv('NOVA_ENV_FILE') ?: null,
    __DIR__ . '/.env',
]);

foreach ($environmentFiles as $environmentFile) {
    if (is_file($environmentFile)) {
        Environment::load($environmentFile);
        break;
    }
}

function nova_database(): PDO
{
    return Connection::get();
}
