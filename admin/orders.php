<?php

declare(strict_types=1);

use Nova\Repository\AdminOrderRepository;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$term = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
if (mb_strlen($term, 'UTF-8') > 100) {
    $term = mb_substr($term, 0, 100, 'UTF-8');
}
$status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
if (!in_array($status, ['', 'pending', 'confirmed', 'cancelled'], true)) {
    $status = '';
}
$orders = (new AdminOrderRepository(nova_database()))->search($term === '' ? null : $term, $status === '' ? null : $status);
admin_header('سفارش‌ها', 'orders');
?>
<div class="page-head"><div><h1>سفارش‌ها</h1><p>مشاهده جزئیات و مدیریت وضعیت سفارش‌های ثبت‌شده</p></div></div>
<form class="filters" method="get"><input name="q" value="<?= admin_e($term) ?>" maxlength="100" placeholder="شماره سفارش، نام یا موبایل"><select name="status"><option value="">همه وضعیت‌ها</option><?php foreach (['pending'=>'جدید','confirmed'=>'تأییدشده','cancelled'=>'لغوشده'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select><button class="btn btn--soft" type="submit">اعمال فیلتر</button></form>
<section class="panel">
<?php if ($orders === []): ?><div class="empty">سفارشی مطابق فیلتر پیدا نشد.</div><?php else: ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>شماره سفارش</th><th>مشتری</th><th>موبایل</th><th>تعداد</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td><a href="order.php?id=<?= (int) $order['id'] ?>"><?= admin_e($order['order_number']) ?></a></td><td><?= admin_e($order['customer_name']) ?></td><td class="ltr"><?= admin_e($order['customer_phone']) ?></td><td><?= number_format((int) $order['total_quantity']) ?></td><td><?= admin_money($order['total_amount']) ?></td><td><span class="badge badge--<?= admin_e($order['status']) ?>"><?= admin_e(admin_status_label($order['status'])) ?></span></td><td class="ltr"><?= admin_e($order['created_at']) ?></td></tr><?php endforeach ?>
</tbody></table></div><?php endif ?></section>
<?php admin_footer(); ?>
