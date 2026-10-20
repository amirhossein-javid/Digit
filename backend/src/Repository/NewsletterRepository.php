<?php

declare(strict_types=1);

namespace Nova\Repository;

use Nova\Exception\ApiException;
use PDO;

final class NewsletterRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return array{email: string, duplicate: bool} */
    public function subscribe(string $email, string $clientHash): array
    {
        $rate = $this->database->prepare(
            'SELECT COUNT(*) FROM newsletter_subscribers
             WHERE client_hash = :client_hash AND created_at >= (UTC_TIMESTAMP() - INTERVAL 1 HOUR)'
        );
        $rate->execute(['client_hash' => $clientHash]);
        if ((int) $rate->fetchColumn() >= 20) {
            throw new ApiException(
                'rate_limited',
                'تعداد درخواست‌های عضویت زیاد است. کمی بعد دوباره تلاش کنید.',
                429
            );
        }

        $statement = $this->database->prepare(
            'INSERT INTO newsletter_subscribers (email, is_active, client_hash) VALUES (:email, 1, :client_hash)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), is_active = 1'
        );
        $statement->execute(['email' => $email, 'client_hash' => $clientHash]);
        return ['email' => $email, 'duplicate' => $statement->rowCount() === 0];
    }
}
