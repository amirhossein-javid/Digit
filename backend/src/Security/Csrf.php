<?php

declare(strict_types=1);

namespace Nova\Security;

final class Csrf
{
    public static function token(): string
    {
        if (!isset($_SESSION['admin_csrf']) || !is_string($_SESSION['admin_csrf'])) {
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['admin_csrf'];
    }

    public static function rotate(): void
    {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    public static function validate(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['admin_csrf'])
            && is_string($_SESSION['admin_csrf'])
            && hash_equals($_SESSION['admin_csrf'], $token);
    }
}
