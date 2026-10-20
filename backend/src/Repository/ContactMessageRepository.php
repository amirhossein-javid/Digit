<?php

declare(strict_types=1);

namespace Nova\Repository;

use Nova\Exception\ApiException;
use PDO;

final class ContactMessageRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @param array{name: string, email: string, subject: string, message: string} $message */
    public function create(array $message, string $clientHash): int
    {
        $rate = $this->database->prepare(
            'SELECT COUNT(*) FROM contact_messages
             WHERE client_hash = :client_hash AND created_at >= (UTC_TIMESTAMP() - INTERVAL 15 MINUTE)'
        );
        $rate->execute(['client_hash' => $clientHash]);
        if ((int) $rate->fetchColumn() >= 5) {
            throw new ApiException('rate_limited', 'تعداد پیام‌های ارسالی زیاد است. کمی بعد دوباره تلاش کنید.', 429);
        }

        $statement = $this->database->prepare(
            'INSERT INTO contact_messages
                (sender_name, sender_email, subject, message, client_hash)
             VALUES (:name, :email, :subject, :message, :client_hash)'
        );
        $statement->execute($message + ['client_hash' => $clientHash]);

        return (int) $this->database->lastInsertId();
    }
}
