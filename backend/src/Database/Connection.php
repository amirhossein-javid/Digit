<?php

declare(strict_types=1);

namespace Nova\Database;

use Nova\Config\Environment;
use PDO;

final class Connection
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = Environment::require('DB_HOST');
        $port = self::validPort(Environment::get('DB_PORT', '3306'));
        $database = Environment::require('DB_NAME');
        $charset = Environment::get('DB_CHARSET', 'utf8mb4') ?? 'utf8mb4';

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new \RuntimeException('Database name contains unsupported characters.');
        }

        if (!in_array($charset, ['utf8mb4', 'utf8'], true)) {
            throw new \RuntimeException('Unsupported database charset.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        self::$instance = new PDO(
            $dsn,
            Environment::require('DB_USER'),
            Environment::require('DB_PASS'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );

        return self::$instance;
    }

    private static function validPort(?string $port): int
    {
        $validated = filter_var($port, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535],
        ]);

        if ($validated === false) {
            throw new \RuntimeException('Database port is invalid.');
        }

        return $validated;
    }
}
