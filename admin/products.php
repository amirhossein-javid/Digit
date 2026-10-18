<?php

declare(strict_types=1);

use Nova\Repository\AdminProductRepository;
use Nova\Security\Csrf;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$term = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
if (mb_strlen($term, 'UTF-8') > 100) {
    $term = mb_substr($term, 0, 100, 'UTF-8');
}
$products = (new AdminProductRepository(nova_database()))->search($term === '' ? null : $term);
admin_header('محصولات', 'products');
?>
<div class="page-head"><div><h1>محصولات</h1><p>افزودن، ویرایش و کنترل وضعیت نمایش محصولات</p></div><a class="btn btn--primary" href="product.php">افزودن محصول</a></div>
<form class="filters" method="get"><input name="q" value="<?= admin_e($term) ?>" maxlength="100" placeholder="جستجو در نام، نامک یا دسته‌بندی"><span></span><button class="btn btn--soft" type="submit">جستجو</button></form>
<section class="panel">
<?php if ($products === []): ?><div class="empty">محصولی مطابق جستجو پیدا نشد.</div><?php else: ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>محصول</th><th>دسته‌بندی</th><th>قیمت</th><th>موجودی</th><th>نمایش</th><th>عملیات</th></tr></thead><tbody>
<?php foreach ($products as $product): ?><tr>
  <td><strong><?= admin_e($product['name']) ?></strong><br><small class="muted ltr"><?= admin_e($product['slug']) ?></small></td>
  <td><?= admin_e($product['category']) ?></td><td><?= admin_money($product['price']) ?></td><td><?= admin_e(['inquiry'=>'استعلام','in_stock'=>'موجود','out_of_stock'=>'ناموجود'][$product['stock_status']] ?? $product['stock_status']) ?></td>
  <td><span class="badge badge--<?= (bool) $product['is_active'] ? 'active' : 'inactive' ?>"><?= (bool) $product['is_active'] ? 'فعال' : 'غیرفعال' ?></span></td>
  <td><div class="actions"><a class="btn btn--soft" href="product.php?id=<?= (int) $product['id'] ?>">ویرایش</a><form method="post" action="product-toggle.php"><input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="active" value="<?= (bool) $product['is_active'] ? '0' : '1' ?>"><button class="btn <?= (bool) $product['is_active'] ? 'btn--danger' : 'btn--soft' ?>" type="submit"><?= (bool) $product['is_active'] ? 'غیرفعال‌سازی' : 'فعال‌سازی' ?></button></form></div></td>
</tr><?php endforeach ?>
</tbody></table></div><?php endif ?></section>
<?php admin_footer(); ?>
