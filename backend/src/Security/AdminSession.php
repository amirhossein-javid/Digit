<?php

declare(strict_types=1);

namespace Nova\Security;

final class AdminSession
{
    private const IDLE_TIMEOUT = 1800;
    private const REGENERATE_INTERVAL = 900;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        $scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php')));
        $cookiePath = $scriptDirectory === '/' ? '/admin' : rtrim($scriptDirectory, '/');
        session_name('NOVA_ADMIN');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookiePath,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();

        $now = time();
        if (isset($_SESSION['admin_id'], $_SESSION['admin_last_activity'])
            && $now - (int) $_SESSION['admin_last_activity'] > self::IDLE_TIMEOUT) {
            self::logout();
            session_start();
        }

        if (isset($_SESSION['admin_id'])) {
            $_SESSION['admin_last_activity'] = $now;
            $regeneratedAt = (int) ($_SESSION['admin_regenerated_at'] ?? 0);
            if ($now - $regeneratedAt >= self::REGENERATE_INTERVAL) {
                session_regenerate_id(true);
                $_SESSION['admin_regenerated_at'] = $now;
            }
        }
    }

    public static function login(int $adminId, string $username): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_regenerated_at'] = time();
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => (bool) $parameters['secure'],
                'httponly' => (bool) $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?: 'Strict',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function id(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
    }

    public static function username(): ?string
    {
        return isset($_SESSION['admin_username']) ? (string) $_SESSION['admin_username'] : null;
    }
}
