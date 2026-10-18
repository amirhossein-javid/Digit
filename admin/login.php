<?php

declare(strict_types=1);

use Nova\Repository\AdminRepository;
use Nova\Security\AdminSession;
use Nova\Security\Csrf;
use Nova\Service\AdminAuthService;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

if (AdminSession::id() !== null) {
    admin_redirect('index.php');
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_require_csrf();
    $username = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $result = (new AdminAuthService(new AdminRepository(nova_database())))->attempt(
        $username,
        $password,
        (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
    );
    if ($result['success']) {
        admin_redirect('index.php');
    }
    $error = $result['message'];
}

$flash = admin_take_flash();
admin_auth_header('ورود');
?>
<h1>ورود به مدیریت</h1><p>برای مدیریت محصولات و سفارش‌ها وارد شوید.</p>
<?php if ($flash !== null): ?><div class="notice notice--<?= admin_e($flash['type']) ?>" role="status"><?= admin_e($flash['message']) ?></div><?php endif ?>
<?php if ($error !== null): ?><div class="notice notice--error" role="alert"><?= admin_e($error) ?></div><?php endif ?>
<form method="post">
  <input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>">
  <label class="field"><span>نام کاربری</span><input class="ltr" name="username" required maxlength="50" autocomplete="username" autofocus></label>
  <label class="field"><span>رمز عبور</span><input class="ltr" type="password" name="password" required maxlength="256" autocomplete="current-password"></label>
  <button class="btn btn--primary" type="submit">ورود امن</button>
</form>
<?php admin_auth_footer(); ?>
