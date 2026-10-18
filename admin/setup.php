<?php

declare(strict_types=1);

use Nova\Config\Environment;
use Nova\Repository\AdminRepository;
use Nova\Security\Csrf;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

$repository = new AdminRepository(nova_database());
if ($repository->count() > 0) {
    admin_redirect('login.php');
}

$errors = [];
$username = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_require_csrf();
    $configuredToken = Environment::get('ADMIN_SETUP_TOKEN');
    $appKey = Environment::get('APP_KEY');
    $submittedToken = is_string($_POST['setup_token'] ?? null) ? $_POST['setup_token'] : '';
    $username = strtolower(trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : ''));
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirmation = is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : '';

    if (!is_string($configuredToken) || strlen($configuredToken) < 32 || !hash_equals($configuredToken, $submittedToken)) {
        $errors[] = 'توکن راه‌اندازی معتبر نیست.';
    }
    if (!is_string($appKey) || strlen($appKey) < 32) {
        $errors[] = 'کلید برنامه باید در تنظیمات خصوصی و با حداقل ۳۲ کاراکتر تنظیم شود.';
    }
    if (!preg_match('/^[a-z0-9._-]{3,50}$/', $username)) {
        $errors[] = 'نام کاربری باید ۳ تا ۵۰ کاراکتر و شامل حروف انگلیسی، عدد، نقطه، خط تیره یا زیرخط باشد.';
    }
    if (strlen($password) < 12 || strlen($password) > 256 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'رمز عبور باید حداقل ۱۲ کاراکتر و شامل حرف و عدد باشد.';
    }
    if (!hash_equals($password, $confirmation)) {
        $errors[] = 'تکرار رمز عبور یکسان نیست.';
    }

    if ($errors === []) {
        $created = $repository->createFirst($username, password_hash($password, PASSWORD_DEFAULT));
        if ($created === null) {
            admin_redirect('login.php');
        }
        admin_flash('success', 'مدیر اولیه ساخته شد. اکنون وارد شوید.');
        admin_redirect('login.php');
    }
}

admin_auth_header('راه‌اندازی مدیر');
?>
<h1>ساخت مدیر اولیه</h1>
<p>این فرم پس از ایجاد اولین مدیر به‌طور خودکار غیرفعال می‌شود.</p>
<?php if ($errors !== []): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= admin_e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
<form method="post" autocomplete="off">
  <input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>">
  <label class="field"><span>توکن راه‌اندازی</span><input class="ltr" type="password" name="setup_token" required autocomplete="off"></label>
  <label class="field"><span>نام کاربری</span><input class="ltr" name="username" required minlength="3" maxlength="50" value="<?= admin_e($username) ?>" autocomplete="username"></label>
  <label class="field"><span>رمز عبور</span><input class="ltr" type="password" name="password" required minlength="12" maxlength="256" autocomplete="new-password"></label>
  <label class="field"><span>تکرار رمز عبور</span><input class="ltr" type="password" name="password_confirmation" required minlength="12" maxlength="256" autocomplete="new-password"></label>
  <button class="btn btn--primary" type="submit">ایجاد مدیر</button>
  <div class="auth-note">توکن را با متغیر <span class="ltr">ADMIN_SETUP_TOKEN</span> و حداقل ۳۲ کاراکتر در فایل محیطی خصوصی تنظیم کنید.</div>
</form>
<?php admin_auth_footer(); ?>
