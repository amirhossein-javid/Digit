<?php

declare(strict_types=1);

use Nova\Exception\AdminValidationException;
use Nova\Repository\AdminCategoryRepository;
use Nova\Security\Csrf;
use Nova\Service\AdminCategoryService;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$repository = new AdminCategoryRepository(nova_database());
$id = isset($_GET['id']) ? admin_positive_id($_GET['id']) : null;
if (isset($_GET['id']) && $id === null) {
    http_response_code(404);
    exit('دسته‌بندی پیدا نشد.');
}
$category = $id === null ? null : $repository->find($id);
if ($id !== null && $category === null) {
    http_response_code(404);
    exit('دسته‌بندی پیدا نشد.');
}
$errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_require_csrf();
    try {
        (new AdminCategoryService($repository))->save($id, $_POST);
        admin_flash('success', $id === null ? 'دسته‌بندی ایجاد شد.' : 'دسته‌بندی به‌روزرسانی شد.');
        admin_redirect('categories.php');
    } catch (AdminValidationException $exception) {
        $errors = $exception->errors();
    }
}
$field = static function (string $key, mixed $fallback = '') use ($category): mixed {
    $posted = $_POST[$key] ?? null;
    return (is_string($posted) || is_int($posted)) ? $posted : ($category[$key] ?? $fallback);
};
$categories = $repository->all();
admin_header('دسته‌بندی‌ها', 'categories');
?>
<div class="page-head"><div><h1>دسته‌بندی‌ها</h1><p>دسته‌های قابل انتخاب برای محصولات را مدیریت کنید.</p></div></div>
<div class="split-layout"><section class="form-card"><h2><?= $id === null ? 'دسته‌بندی جدید' : 'ویرایش دسته‌بندی' ?></h2>
<?php if ($errors !== []): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= admin_e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
<form method="post"><input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>"><div class="form-grid"><label class="field"><span>نام</span><input name="name" required minlength="2" maxlength="100" value="<?= admin_e($field('name')) ?>"></label><label class="field"><span>نامک انگلیسی</span><input class="ltr" name="slug" required maxlength="100" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" value="<?= admin_e($field('slug')) ?>"></label><label class="field"><span>ترتیب نمایش</span><input class="ltr" type="number" name="position" min="0" max="65535" required value="<?= admin_e($field('position', 0)) ?>"></label></div><div class="form-actions"><button class="btn btn--primary" type="submit">ذخیره دسته‌بندی</button><?php if ($id !== null): ?><a class="btn btn--soft" href="categories.php">انصراف</a><?php endif ?></div></form></section>
<section class="panel"><div class="table-wrap"><table class="data-table"><thead><tr><th>نام</th><th>نامک</th><th>محصولات</th><th>ترتیب</th><th>عملیات</th></tr></thead><tbody><?php foreach ($categories as $item): ?><tr><td><?= admin_e($item['name']) ?></td><td class="ltr"><?= admin_e($item['slug']) ?></td><td><?= number_format((int) $item['product_count']) ?></td><td><?= number_format((int) $item['position']) ?></td><td><a class="btn btn--soft" href="categories.php?id=<?= (int) $item['id'] ?>">ویرایش</a></td></tr><?php endforeach ?></tbody></table></div></section></div>
<?php admin_footer(); ?>
