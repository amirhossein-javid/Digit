<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Config\Environment;
use Nova\Repository\AdminRepository;
use Nova\Security\AdminSession;
use Nova\Security\Csrf;

final class AdminAuthService
{
    private const MAX_ATTEMPTS = 5;
    private const DUMMY_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    public function __construct(private readonly AdminRepository $admins)
    {
    }

    /** @return array{success: bool, message: string} */
    public function attempt(string $username, string $password, string $ipAddress): array
    {
        $username = strtolower(trim($username));
        $appKey = Environment::require('APP_KEY');
        if (strlen($appKey) < 32) {
            throw new \RuntimeException('APP_KEY must contain at least 32 characters.');
        }
        $identifier = hash_hmac('sha256', $username . '|' . $ipAddress, $appKey);

        if ($this->admins->failedAttempts($identifier) >= self::MAX_ATTEMPTS) {
            return ['success' => false, 'message' => 'تعداد تلاش‌ها بیش از حد مجاز است. ۱۵ دقیقه دیگر دوباره تلاش کنید.'];
        }

        $credentialsHaveValidShape = preg_match('/^[a-z0-9._-]{3,50}$/', $username) === 1
            && strlen($password) >= 1
            && strlen($password) <= 256;
        $admin = $credentialsHaveValidShape ? $this->admins->findByUsername($username) : null;
        $hash = is_array($admin) ? (string) $admin['password_hash'] : self::DUMMY_HASH;
        $valid = password_verify($credentialsHaveValidShape ? $password : 'invalid-credentials', $hash);

        if (!$valid || $admin === null || !(bool) $admin['is_active']) {
            $this->admins->addFailedAttempt($identifier);
            return ['success' => false, 'message' => 'نام کاربری یا رمز عبور صحیح نیست.'];
        }

        $replacement = password_needs_rehash($hash, PASSWORD_DEFAULT)
            ? password_hash($password, PASSWORD_DEFAULT)
            : null;
        $this->admins->recordLogin((int) $admin['id'], $replacement);
        $this->admins->clearFailedAttempts($identifier);
        AdminSession::login((int) $admin['id'], (string) $admin['username']);
        Csrf::rotate();

        return ['success' => true, 'message' => ''];
    }
}
