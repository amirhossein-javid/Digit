<?php

declare(strict_types=1);

use Nova\Security\AdminSession;
use Nova\Security\Csrf;

$bootstrapCandidates = [
    dirname(__DIR__, 2) . '/nova-private/bootstrap.php',
    dirname(__DIR__) . '/backend/bootstrap.php',
];
$bootstrapLoaded = false;
foreach ($bootstrapCandidates as $bootstrapFile) {
    if (is_file($bootstrapFile)) {
        require $bootstrapFile;
        $bootstrapLoaded = true;
        break;
    }
}
if (!$bootstrapLoaded) {
    http_response_code(500);
    exit('تنظیمات پنل مدیریت در دسترس نیست.');
}

AdminSession::start();

header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('Content-Type: text/html; charset=utf-8');

set_exception_handler(static function (Throwable $exception): void {
    error_log(sprintf('[Nova Admin] %s in %s:%d', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><title>خطا | مدیریت نُوا</title>'
        . '<link rel="stylesheet" href="assets/admin.css"><body class="auth-page"><main class="auth-card"><h1>خطایی رخ داد</h1>'
        . '<p>امکان پردازش درخواست وجود ندارد. لطفاً دوباره تلاش کنید.</p></main></body></html>';
});

function admin_e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function admin_require_auth(): int
{
    $id = AdminSession::id();
    if ($id === null) {
        admin_redirect('login.php');
    }
    return $id;
}

function admin_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method Not Allowed');
    }
}

function admin_require_csrf(): void
{
    if (!Csrf::validate($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit('درخواست منقضی یا نامعتبر است. صفحه را تازه‌سازی کنید.');
    }
}

function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type: string, message: string}|null */
function admin_take_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    return is_array($flash) && isset($flash['type'], $flash['message']) ? $flash : null;
}

function admin_positive_id(mixed $value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : (int) $id;
}

function admin_money(int|string $value): string
{
    return number_format((int) $value) . ' تومان';
}

function admin_status_label(string $status): string
{
    return ['pending' => 'جدید', 'confirmed' => 'تأییدشده', 'cancelled' => 'لغوشده'][$status] ?? $status;
}
