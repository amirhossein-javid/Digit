<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;
use Throwable;

final class AdminProductRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return list<array<string, mixed>> */
    public function search(?string $term): array
    {
        $where = '';
        $parameters = [];
        if ($term !== null) {
            $where = ' WHERE p.name LIKE :name OR p.slug LIKE :slug OR c.name LIKE :category';
            $value = '%' . $term . '%';
            $parameters = ['name' => $value, 'slug' => $value, 'category' => $value];
        }
        $statement = $this->database->prepare(
            'SELECT p.id, p.name, p.slug, p.price, p.stock_status, p.is_active, p.updated_at,
                    c.name AS category
             FROM products p INNER JOIN categories c ON c.id = p.category_id' . $where . '
             ORDER BY p.id DESC LIMIT 200'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();
        if ($product === false) {
            return null;
        }

        $specs = $this->database->prepare(
            'SELECT spec_key, spec_value FROM product_specs WHERE product_id = :id ORDER BY position, id'
        );
        $specs->execute(['id' => $id]);
        $product['specs'] = $specs->fetchAll();

        return $product;
    }

    /** @return list<array{id: int|string, name: string}> */
    public function categories(): array
    {
        return $this->database->query('SELECT id, name FROM categories ORDER BY position, name')->fetchAll();
    }

    public function categoryExists(int $id): bool
    {
        $statement = $this->database->prepare('SELECT 1 FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    public function slugExists(string $slug, ?int $exceptId): bool
    {
        $sql = 'SELECT 1 FROM products WHERE slug = :slug';
        $parameters = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }
        $statement = $this->database->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    /** @param array<string, mixed> $data
     *  @param list<array{key: string, value: string}> $specs
     */
    public function save(?int $id, array $data, array $specs): int
    {
        try {
            $this->database->beginTransaction();
            if ($id === null) {
                $statement = $this->database->prepare(
                    'INSERT INTO products
                        (category_id, name, slug, description, price, image_webp, image_avif,
                         is_featured, is_new, is_active, stock_status)
                     VALUES
                        (:category_id, :name, :slug, :description, :price, :image_webp, :image_avif,
                         :is_featured, :is_new, 1, :stock_status)'
                );
                $statement->execute($data);
                $id = (int) $this->database->lastInsertId();
            } else {
                $statement = $this->database->prepare(
                    'UPDATE products SET category_id = :category_id, name = :name, slug = :slug,
                        description = :description, price = :price, image_webp = :image_webp,
                        image_avif = :image_avif, is_featured = :is_featured, is_new = :is_new,
                        stock_status = :stock_status WHERE id = :id'
                );
                $statement->execute($data + ['id' => $id]);
                $this->database->prepare('DELETE FROM product_specs WHERE product_id = :id')->execute(['id' => $id]);
            }

            $specStatement = $this->database->prepare(
                'INSERT INTO product_specs (product_id, spec_key, spec_value, position)
                 VALUES (:product_id, :spec_key, :spec_value, :position)'
            );
            foreach ($specs as $position => $spec) {
                $specStatement->execute([
                    'product_id' => $id,
                    'spec_key' => $spec['key'],
                    'spec_value' => $spec['value'],
                    'position' => $position,
                ]);
            }

            $this->database->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function setActive(int $id, bool $active): bool
    {
        $statement = $this->database->prepare('UPDATE products SET is_active = :active WHERE id = :id');
        $statement->bindValue(':active', $active ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0 || $this->find($id) !== null;
    }
}
