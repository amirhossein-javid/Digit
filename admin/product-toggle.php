<?php

declare(strict_types=1);

use Nova\Repository\AdminProductRepository;

require __DIR__ . '/_bootstrap.php';

admin_require_auth();
admin_require_post();
admin_require_csrf();
$id = admin_positive_id($_POST['id'] ?? null);
$active = $_POST['active'] ?? null;
if ($id === null || !is_string($active) || !in_array($active, ['0', '1'], true)) {
    http_response_code(422);
    exit('اطلاعات محصول معتبر نیست.');
}
$updated = (new AdminProductRepository(nova_database()))->setActive($id, $active === '1');
admin_flash($updated ? 'success' : 'error', $updated ? 'وضعیت محصول به‌روزرسانی شد.' : 'محصول پیدا نشد.');
admin_redirect('products.php');
