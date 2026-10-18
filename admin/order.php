<?php

declare(strict_types=1);

use Nova\Repository\AdminOrderRepository;
use Nova\Security\Csrf;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

$adminId = admin_require_auth();
$id = admin_positive_id($_GET['id'] ?? null);
if ($id === null) {
    http_response_code(404);
    exit('سفارش پیدا نشد.');
}
$repository = new AdminOrderRepository(nova_database());
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_require_csrf();
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    if (!in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
        http_response_code(422);
        exit('وضعیت سفارش معتبر نیست.');
    }
    if (!$repository->updateStatus($id, $status, $adminId)) {
        http_response_code(404);
        exit('سفارش پیدا نشد.');
    }
    admin_flash('success', 'وضعیت سفارش به‌روزرسانی شد.');
    admin_redirect('order.php?id=' . $id);
}
$order = $repository->find($id);
if ($order === null) {
    http_response_code(404);
    exit('سفارش پیدا نشد.');
}
admin_header('جزئیات سفارش', 'orders');
?>
<div class="page-head"><div><h1>سفارش <?= admin_e($order['order_number']) ?></h1><p>ثبت‌شده در <span class="ltr"><?= admin_e($order['created_at']) ?></span></p></div><a class="btn btn--soft" href="orders.php">بازگشت</a></div>
<div class="order-layout"><div>
  <section class="panel"><div class="panel-head"><h2>اقلام سفارش</h2><span class="badge badge--<?= admin_e($order['status']) ?>"><?= admin_e(admin_status_label($order['status'])) ?></span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>محصول</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th></tr></thead><tbody><?php foreach ($order['items'] as $item): ?><tr><td><?= admin_e($item['product_name']) ?></td><td><?= admin_money($item['unit_price']) ?></td><td><?= number_format((int) $item['quantity']) ?></td><td><?= admin_money($item['line_total']) ?></td></tr><?php endforeach ?></tbody><tfoot><tr><th colspan="3">مجموع سفارش</th><th><?= admin_money($order['total_amount']) ?></th></tr></tfoot></table></div></section>
  <section class="panel panel-spaced"><div class="panel-head"><h2>اطلاعات دریافت‌کننده</h2></div><div class="info-grid"><div class="info-item"><small>نام</small><?= admin_e($order['customer_name']) ?></div><div class="info-item"><small>موبایل</small><span class="ltr"><?= admin_e($order['customer_phone']) ?></span></div><div class="info-item"><small>ایمیل</small><?= admin_e($order['customer_email'] ?: '—') ?></div><div class="info-item"><small>شهر</small><?= admin_e($order['city']) ?></div><div class="info-item"><small>کد پستی</small><?= admin_e($order['postal_code'] ?: '—') ?></div><div class="info-item"><small>نشانی</small><?= nl2br(admin_e($order['address'])) ?></div></div></section>
</div><aside>
  <section class="form-card"><h2>تغییر وضعیت</h2><form method="post"><input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>"><label class="field"><span>وضعیت سفارش</span><select name="status"><?php foreach (['pending'=>'جدید','confirmed'=>'تأییدشده','cancelled'=>'لغوشده'] as $key=>$label): ?><option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select></label><div class="form-actions"><button class="btn btn--primary" type="submit">ذخیره وضعیت</button></div></form></section>
  <section class="panel panel-spaced"><div class="panel-head"><h2>تاریخچه وضعیت</h2></div><?php if ($order['history'] === []): ?><div class="empty">هنوز تغییری ثبت نشده است.</div><?php else: ?><ul class="timeline"><?php foreach ($order['history'] as $item): ?><li><strong><?= admin_e(admin_status_label($item['previous_status'])) ?> ← <?= admin_e(admin_status_label($item['new_status'])) ?></strong><br><small><?= admin_e($item['username']) ?> · <span class="ltr"><?= admin_e($item['created_at']) ?></span></small></li><?php endforeach ?></ul><?php endif ?></section>
</aside></div>
<?php admin_footer(); ?>
