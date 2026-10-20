<?php

declare(strict_types=1);

use Nova\Repository\AdminContactMessageRepository;
use Nova\Security\Csrf;

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_layout.php';

admin_require_auth();
$id = admin_positive_id($_GET['id'] ?? null);
if ($id === null) {
    http_response_code(404);
    exit('پیام پیدا نشد.');
}
$repository = new AdminContactMessageRepository(nova_database());
$message = $repository->find($id);
if ($message === null) {
    http_response_code(404);
    exit('پیام پیدا نشد.');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    admin_require_csrf();
    $status = isset($_POST['status']) && is_string($_POST['status']) ? $_POST['status'] : '';
    if (!in_array($status, ['new', 'read'], true)) {
        http_response_code(422);
        exit('وضعیت پیام معتبر نیست.');
    }
    $repository->setStatus($id, $status);
    admin_flash('success', 'وضعیت پیام به‌روزرسانی شد.');
    admin_redirect('message.php?id=' . $id);
}
admin_header('جزئیات پیام', 'messages');
?>
<div class="page-head"><div><h1>جزئیات پیام</h1><p><?= admin_e(admin_contact_subject_label($message['subject'])) ?></p></div><a class="btn btn--soft" href="messages.php">بازگشت</a></div>
<section class="panel"><div class="info-grid">
  <div class="info-item"><small>فرستنده</small><strong><?= admin_e($message['sender_name']) ?></strong></div>
  <div class="info-item"><small>ایمیل</small><a class="ltr" href="mailto:<?= admin_e($message['sender_email']) ?>"><?= admin_e($message['sender_email']) ?></a></div>
  <div class="info-item"><small>موضوع</small><strong><?= admin_e(admin_contact_subject_label($message['subject'])) ?></strong></div>
  <div class="info-item"><small>زمان ثبت</small><strong class="ltr"><?= admin_e($message['created_at']) ?></strong></div>
</div><div class="message-body"><?= nl2br(admin_e($message['message'])) ?></div></section>
<form class="form-actions" method="post"><input type="hidden" name="_csrf" value="<?= admin_e(Csrf::token()) ?>"><input type="hidden" name="status" value="<?= $message['status'] === 'new' ? 'read' : 'new' ?>"><button class="btn btn--primary" type="submit"><?= $message['status'] === 'new' ? 'علامت‌گذاری به‌عنوان خوانده‌شده' : 'علامت‌گذاری به‌عنوان خوانده‌نشده' ?></button></form>
<?php admin_footer(); ?>
