<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;

final class AdminCategoryRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->database->query(
            'SELECT c.id, c.name, c.slug, c.position, COUNT(p.id) AS product_count
             FROM categories c LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id, c.name, c.slug, c.position
             ORDER BY c.position, c.id'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->database->prepare('SELECT id, name, slug, position FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function valueExists(string $field, string $value, ?int $exceptId): bool
    {
        if (!in_array($field, ['name', 'slug'], true)) {
            throw new \InvalidArgumentException('Unsupported category field.');
        }
        $sql = 'SELECT 1 FROM categories WHERE ' . $field . ' = :value';
        $parameters = ['value' => $value];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }
        $statement = $this->database->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function save(?int $id, string $name, string $slug, int $position): int
    {
        if ($id === null) {
            $statement = $this->database->prepare(
                'INSERT INTO categories (name, slug, position) VALUES (:name, :slug, :position)'
            );
            $statement->execute(['name' => $name, 'slug' => $slug, 'position' => $position]);
            return (int) $this->database->lastInsertId();
        }

        $statement = $this->database->prepare(
            'UPDATE categories SET name = :name, slug = :slug, position = :position WHERE id = :id'
        );
        $statement->execute(['name' => $name, 'slug' => $slug, 'position' => $position, 'id' => $id]);
        return $id;
    }
}
