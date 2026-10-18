<?php

declare(strict_types=1);

use Nova\Repository\AdminDashboardRepository;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$repository = new AdminDashboardRepository(nova_database());
$counts = $repository->counts();
$orders = $repository->recentOrders();
admin_header('داشبورد', 'dashboard');
?>
<div class="page-head"><div><h1>داشبورد</h1><p>نمای کلی وضعیت کاتالوگ و سفارش‌ها</p></div></div>
<section class="stats-grid" aria-label="آمار فروشگاه">
  <article class="stat-card"><span>محصولات فعال</span><strong><?= number_format($counts['products']) ?></strong></article>
  <article class="stat-card"><span>همه سفارش‌ها</span><strong><?= number_format($counts['orders']) ?></strong></article>
  <article class="stat-card"><span>سفارش‌های جدید</span><strong><?= number_format($counts['pendingOrders']) ?></strong></article>
</section>
<section class="panel"><div class="panel-head"><h2>آخرین سفارش‌ها</h2><a class="btn btn--soft" href="orders.php">همه سفارش‌ها</a></div>
<?php if ($orders === []): ?><div class="empty">هنوز سفارشی ثبت نشده است.</div><?php else: ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>شماره</th><th>مشتری</th><th>تعداد</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td><a href="order.php?id=<?= (int) $order['id'] ?>"><?= admin_e($order['order_number']) ?></a></td><td><?= admin_e($order['customer_name']) ?></td><td><?= number_format((int) $order['total_quantity']) ?></td><td><?= admin_money($order['total_amount']) ?></td><td><span class="badge badge--<?= admin_e($order['status']) ?>"><?= admin_e(admin_status_label($order['status'])) ?></span></td><td class="ltr"><?= admin_e($order['created_at']) ?></td></tr><?php endforeach ?>
</tbody></table></div><?php endif ?></section>
<?php admin_footer(); ?>
