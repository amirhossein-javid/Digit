<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;

final class AdminContactMessageRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return list<array<string, mixed>> */
    public function search(?string $term, ?string $status): array
    {
        $where = [];
        $parameters = [];
        if ($term !== null) {
            $where[] = '(sender_name LIKE :name OR sender_email LIKE :email OR message LIKE :message)';
            $value = '%' . $term . '%';
            $parameters = ['name' => $value, 'email' => $value, 'message' => $value];
        }
        if ($status !== null) {
            $where[] = 'status = :status';
            $parameters['status'] = $status;
        }
        $sql = 'SELECT id, sender_name, sender_email, subject, status, created_at FROM contact_messages';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $statement = $this->database->prepare($sql . ' ORDER BY id DESC LIMIT 200');
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, sender_name, sender_email, subject, message, status, created_at, read_at
             FROM contact_messages WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function setStatus(int $id, string $status): bool
    {
        $statement = $this->database->prepare(
            "UPDATE contact_messages SET status = :status,
             read_at = CASE WHEN :status_read = 'read' THEN UTC_TIMESTAMP() ELSE NULL END WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'status_read' => $status, 'id' => $id]);
        return $statement->rowCount() > 0 || $this->find($id) !== null;
    }
}
