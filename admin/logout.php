<?php

declare(strict_types=1);

use Nova\Security\AdminSession;

require __DIR__ . '/_bootstrap.php';

admin_require_auth();
admin_require_post();
admin_require_csrf();
AdminSession::logout();
admin_redirect('login.php');
