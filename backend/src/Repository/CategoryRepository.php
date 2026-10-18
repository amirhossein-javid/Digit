<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return list<array{id: int, name: string, slug: string, productCount: int}> */
    public function all(): array
    {
        $statement = $this->database->query(
            'SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
             GROUP BY c.id, c.name, c.slug
             ORDER BY c.position ASC, c.id ASC'
        );

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'productCount' => (int) $row['product_count'],
        ], $statement->fetchAll());
    }
}
