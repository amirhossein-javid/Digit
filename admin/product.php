<?php

declare(strict_types=1);

use Nova\Exception\AdminValidationException;
use Nova\Repository\AdminProductRepository;
use Nova\Security\Csrf;
use Nova\Service\AdminProductService;
use Nova\Service\ImageUploadService;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$repository = new AdminProductRepository(nova_database());
$id = isset($_GET['id']) ? admin_positive_id($_GET['id']) : null;
if (isset($_GET['id']) && $id === null) {
    http_response_code(404);
    exit('محصول پیدا نشد.');
}
$product = $id === null ? null : $repository->find($id);
if ($id !== null && $product === null) {
    http_response_code(404);
    exit('محصول پیدا نشد.');
}
$errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_require_csrf();
    try {
        $savedId = (new AdminProductService($repository, new ImageUploadService()))->save($id, $_POST, $_FILES);
        admin_flash('success', $id === null ? 'محصول جدید ایجاد شد.' : 'محصول با موفقیت به‌روزرسانی شد.');
        admin_redirect('product.php?id=' . $savedId);
    } catch (AdminValidationException $exception) {
        $errors = $exception->errors();
    }
}

$value = static function (string $key, mixed $fallback = '') use ($product): mixed {
    $posted = $_POST[$key] ?? null;
    if (is_string($posted) || is_int($posted)) {
        return $posted;
    }
    return $product[$key] ?? $fallback;
};
$specKeys = isset($_POST['spec_key']) && is_array($_POST['spec_key'])
    ? $_POST['spec_key']
    : array_column($product['specs'] ?? [], 'spec_key');
$specValues = isset($_POST['spec_value']) && is_array($_POST['spec_value'])
    ? $_POST['spec_value']
    : array_column($product['specs'] ?? [], 'spec_value');
if ($specKeys === []) {
    $specKeys = [''];
    $specValues = [''];
}
$categories = $repository->categories();
admin_header($id === null ? 'افزودن محصول' : 'ویرایش محصول', 'products');
?>
<div class="page-head"><div><h1><?= $id === null ? 'افزودن محصول' : 'ویرایش محصول' ?></h1><p>اطلاعات کاتالوگ و تصاویر بهینه محصول را مدیریت کنید.</p></div><a class="btn btn--soft" href="products.php">بازگشت</a></div>
<?php if ($errors !== []): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= admin_e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
<form class="form-card" method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>">
  <div class="form-grid">
    <label class="field"><span>نام محصول</span><input name="name" required minlength="3" maxlength="190" value="<?= admin_e($value('name')) ?>"></label>
    <label class="field"><span>نامک انگلیسی</span><input class="ltr" name="slug" required maxlength="190" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" value="<?= admin_e($value('slug')) ?>"></label>
    <label class="field"><span>دسته‌بندی</span><select name="category_id" required><option value="">انتخاب کنید</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $value('category_id') === (int) $category['id'] ? 'selected' : '' ?>><?= admin_e($category['name']) ?></option><?php endforeach ?></select></label>
    <label class="field"><span>قیمت (تومان)</span><input class="ltr" type="number" name="price" min="1" step="1" required value="<?= admin_e($value('price')) ?>"></label>
    <label class="field"><span>وضعیت موجودی</span><select name="stock_status" required><?php foreach (['inquiry'=>'نیازمند استعلام','in_stock'=>'موجود','out_of_stock'=>'ناموجود'] as $status=>$label): ?><option value="<?= $status ?>" <?= $value('stock_status', 'inquiry') === $status ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select></label>
    <div class="field"><span>برچسب‌ها</span><div class="check-row"><label><input type="checkbox" name="is_featured" value="1" <?= isset($_POST['is_featured']) || (!$_POST && (bool) ($product['is_featured'] ?? false)) ? 'checked' : '' ?>> منتخب</label><label><input type="checkbox" name="is_new" value="1" <?= isset($_POST['is_new']) || (!$_POST && (bool) ($product['is_new'] ?? false)) ? 'checked' : '' ?>> جدید</label></div></div>
    <label class="field field--full"><span>توضیحات</span><textarea name="description" rows="6" required minlength="10" maxlength="5000"><?= admin_e($value('description')) ?></textarea></label>
    <label class="field"><span>تصویر WebP <?= $id === null ? '*' : '' ?></span><input type="file" name="image_webp" accept="image/webp" <?= $id === null ? 'required' : '' ?>><small>حداکثر ۴ مگابایت<?= $product ? '؛ تصویر فعلی: ' . admin_e($product['image_webp']) : '' ?></small></label>
    <label class="field"><span>تصویر AVIF <?= $id === null ? '*' : '' ?></span><input type="file" name="image_avif" accept="image/avif" <?= $id === null ? 'required' : '' ?>><small>حداکثر ۴ مگابایت<?= $product ? '؛ تصویر فعلی: ' . admin_e($product['image_avif']) : '' ?></small></label>
    <div class="field field--full"><span>مشخصات فنی</span><div class="spec-list" data-specs><?php foreach ($specKeys as $index=>$key): ?><div class="spec-row"><input name="spec_key[]" maxlength="100" placeholder="عنوان" value="<?= admin_e(is_string($key) ? $key : '') ?>"><input name="spec_value[]" maxlength="255" placeholder="مقدار" value="<?= admin_e(is_string($specValues[$index] ?? null) ? $specValues[$index] : '') ?>"><button type="button" data-remove-spec aria-label="حذف مشخصه">×</button></div><?php endforeach ?></div><button class="btn btn--soft" type="button" data-add-spec>افزودن مشخصه</button></div>
  </div>
  <div class="form-actions"><button class="btn btn--primary" type="submit">ذخیره محصول</button><a class="btn btn--soft" href="products.php">انصراف</a></div>
</form>
<template id="specRowTemplate"><div class="spec-row"><input name="spec_key[]" maxlength="100" placeholder="عنوان"><input name="spec_value[]" maxlength="255" placeholder="مقدار"><button type="button" data-remove-spec aria-label="حذف مشخصه">×</button></div></template>
<?php admin_footer(); ?>
