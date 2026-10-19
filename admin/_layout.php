<?php

declare(strict_types=1);

use Nova\Security\AdminSession;
use Nova\Security\Csrf;

function admin_header(string $title, string $active = ''): void
{
    $username = admin_e(AdminSession::username() ?? 'مدیر');
    $flash = admin_take_flash();
    $flashMarkup = $flash === null ? '' : sprintf(
        '<div class="notice notice--%s" role="status">%s</div>',
        admin_e($flash['type']),
        admin_e($flash['message'])
    );
    $links = [
        'dashboard' => ['index.php', 'داشبورد'],
        'products' => ['products.php', 'محصولات'],
        'categories' => ['categories.php', 'دسته‌بندی‌ها'],
        'orders' => ['orders.php', 'سفارش‌ها'],
    ];
    $nav = '';
    foreach ($links as $key => [$href, $label]) {
        $current = $key === $active ? ' aria-current="page" class="is-active"' : '';
        $nav .= sprintf('<a href="%s"%s>%s</a>', $href, $current, $label);
    }
    $csrf = admin_e(Csrf::token());
    $safeTitle = admin_e($title);
    echo <<<HTML
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$safeTitle} | مدیریت دیجیت</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
  <header class="admin-topbar">
    <button class="menu-button" type="button" aria-label="بازکردن منو" aria-expanded="false" data-admin-menu>☰</button>
    <a class="admin-brand" href="index.php"><i>×</i><strong>دیجیت</strong><span>مدیریت</span></a>
    <div class="admin-account"><span>{$username}</span><form action="logout.php" method="post"><input type="hidden" name="_csrf" value="{$csrf}"><button type="submit">خروج</button></form></div>
  </header>
  <div class="admin-shell">
    <nav class="admin-nav" aria-label="ناوبری مدیریت" data-admin-nav>{$nav}<a href="../index.html" target="_blank" rel="noopener">مشاهده فروشگاه</a></nav>
    <main class="admin-main">
      {$flashMarkup}
HTML;
}

function admin_footer(): void
{
    echo <<<'HTML'
    </main>
  </div>
  <script src="assets/admin.js"></script>
</body>
</html>
HTML;
}

function admin_auth_header(string $title): void
{
    $safeTitle = admin_e($title);
    echo <<<HTML
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$safeTitle} | مدیریت دیجیت</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth-page"><main class="auth-card">
<a class="admin-brand admin-brand--center" href="../index.html"><i>×</i><strong>دیجیت</strong><span>مدیریت</span></a>
HTML;
}

function admin_auth_footer(): void
{
    echo '</main><script src="assets/admin.js"></script></body></html>';
}
