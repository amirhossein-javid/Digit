<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;

final class AdminRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return array<string, mixed>|null */
    public function findByUsername(string $username): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, username, password_hash, is_active FROM admins WHERE username = :username LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function count(): int
    {
        return (int) $this->database->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    }

    public function createFirst(string $username, string $passwordHash): ?int
    {
        $lock = $this->database->prepare("SELECT GET_LOCK('nova_admin_initial_setup', 5)");
        $lock->execute();
        if ((int) $lock->fetchColumn() !== 1) {
            throw new \RuntimeException('The initial administrator setup lock could not be acquired.');
        }

        try {
            if ($this->count() > 0) {
                return null;
            }
            $statement = $this->database->prepare(
                'INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)'
            );
            $statement->execute(['username' => $username, 'password_hash' => $passwordHash]);

            return (int) $this->database->lastInsertId();
        } finally {
            $this->database->query("SELECT RELEASE_LOCK('nova_admin_initial_setup')");
        }
    }

    public function recordLogin(int $adminId, ?string $newHash = null): void
    {
        $sql = 'UPDATE admins SET last_login_at = UTC_TIMESTAMP()';
        $parameters = ['id' => $adminId];
        if ($newHash !== null) {
            $sql .= ', password_hash = :password_hash';
            $parameters['password_hash'] = $newHash;
        }
        $sql .= ' WHERE id = :id';
        $statement = $this->database->prepare($sql);
        $statement->execute($parameters);
    }

    public function failedAttempts(string $identifierHash): int
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM admin_login_attempts
             WHERE identifier_hash = :identifier_hash
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL 15 MINUTE'
        );
        $statement->execute(['identifier_hash' => $identifierHash]);

        return (int) $statement->fetchColumn();
    }

    public function addFailedAttempt(string $identifierHash): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO admin_login_attempts (identifier_hash) VALUES (:identifier_hash)'
        );
        $statement->execute(['identifier_hash' => $identifierHash]);
        $this->database->exec(
            'DELETE FROM admin_login_attempts WHERE attempted_at < UTC_TIMESTAMP() - INTERVAL 1 DAY'
        );
    }

    public function clearFailedAttempts(string $identifierHash): void
    {
        $statement = $this->database->prepare(
            'DELETE FROM admin_login_attempts WHERE identifier_hash = :identifier_hash'
        );
        $statement->execute(['identifier_hash' => $identifierHash]);
    }
}
