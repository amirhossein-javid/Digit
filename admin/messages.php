<?php

declare(strict_types=1);

use Nova\Repository\AdminContactMessageRepository;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$term = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
if (mb_strlen($term, 'UTF-8') > 100) {
    $term = mb_substr($term, 0, 100, 'UTF-8');
}
$status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
if (!in_array($status, ['', 'new', 'read'], true)) {
    $status = '';
}
$messages = (new AdminContactMessageRepository(nova_database()))->search($term === '' ? null : $term, $status === '' ? null : $status);
admin_header('پیام‌های تماس', 'messages');
?>
<div class="page-head"><div><h1>پیام‌های تماس</h1><p>مشاهده پیام‌های ثبت‌شده از فرم تماس فروشگاه</p></div></div>
<form class="filters" method="get"><input name="q" value="<?= admin_e($term) ?>" maxlength="100" placeholder="نام، ایمیل یا متن پیام"><select name="status"><option value="">همه وضعیت‌ها</option><option value="new" <?= $status === 'new' ? 'selected' : '' ?>>خوانده‌نشده</option><option value="read" <?= $status === 'read' ? 'selected' : '' ?>>خوانده‌شده</option></select><button class="btn btn--soft" type="submit">اعمال فیلتر</button></form>
<section class="panel">
<?php if ($messages === []): ?><div class="empty">پیامی مطابق فیلتر پیدا نشد.</div><?php else: ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>فرستنده</th><th>ایمیل</th><th>موضوع</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>
<?php foreach ($messages as $message): ?><tr><td><a href="message.php?id=<?= (int) $message['id'] ?>"><?= admin_e($message['sender_name']) ?></a></td><td class="ltr"><?= admin_e($message['sender_email']) ?></td><td><?= admin_e(admin_contact_subject_label($message['subject'])) ?></td><td><span class="badge badge--<?= $message['status'] === 'new' ? 'pending' : 'active' ?>"><?= $message['status'] === 'new' ? 'خوانده‌نشده' : 'خوانده‌شده' ?></span></td><td class="ltr"><?= admin_e($message['created_at']) ?></td></tr><?php endforeach ?>
</tbody></table></div><?php endif ?></section>
<?php admin_footer(); ?>
