<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;

final class ProductRepository
{
    private const SORTS = [
        'default' => 'p.id ASC',
        'price-asc' => 'p.price ASC, p.id ASC',
        'price-desc' => 'p.price DESC, p.id ASC',
        'name' => 'p.name ASC, p.id ASC',
        'newest' => 'p.id DESC',
    ];

    public function __construct(private readonly PDO $database)
    {
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function paginate(?string $search, ?string $category, string $sort, int $page, int $limit): array
    {
        $where = ['p.is_active = 1'];
        $parameters = [];

        if ($search !== null) {
            $where[] = '(p.name LIKE :search_name OR p.description LIKE :search_description OR c.name LIKE :search_category)';
            $term = '%' . $search . '%';
            $parameters['search_name'] = $term;
            $parameters['search_description'] = $term;
            $parameters['search_category'] = $term;
        }

        if ($category !== null) {
            if (ctype_digit($category)) {
                $where[] = 'c.id = :category_id';
                $parameters['category_id'] = (int) $category;
            } else {
                $where[] = '(c.slug = :category_slug OR c.name = :category_name)';
                $parameters['category_slug'] = $category;
                $parameters['category_name'] = $category;
            }
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $orderBy = self::SORTS[$sort] ?? self::SORTS['default'];
        $offset = ($page - 1) * $limit;

        $countStatement = $this->database->prepare(
            'SELECT COUNT(*) FROM products p INNER JOIN categories c ON c.id = p.category_id' . $whereSql
        );
        $this->bind($countStatement, $parameters);
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $statement = $this->database->prepare(
            'SELECT p.id, p.name, p.slug, p.description, p.price, p.image_webp, p.image_avif,
                    p.is_featured, p.is_new, p.stock_status, c.name AS category, c.slug AS category_slug
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id' .
             $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset'
        );
        $this->bind($statement, $parameters);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $items = array_map($this->mapProduct(...), $statement->fetchAll());

        return ['items' => $items, 'total' => $total];
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT p.id, p.name, p.slug, p.description, p.price, p.image_webp, p.image_avif,
                    p.is_featured, p.is_new, p.stock_status, c.name AS category, c.slug AS category_slug
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id AND p.is_active = 1
             LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        $product = $this->mapProduct($row);
        $specificationStatement = $this->database->prepare(
            'SELECT spec_key, spec_value
             FROM product_specs
             WHERE product_id = :product_id
             ORDER BY position ASC, id ASC'
        );
        $specificationStatement->bindValue(':product_id', $id, PDO::PARAM_INT);
        $specificationStatement->execute();
        $product['specs'] = array_map(
            static fn (array $spec): array => [$spec['spec_key'], $spec['spec_value']],
            $specificationStatement->fetchAll()
        );

        return $product;
    }

    /** @param array<string, string|int> $parameters */
    private function bind(\PDOStatement $statement, array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    private function mapProduct(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'category' => $row['category'],
            'categorySlug' => $row['category_slug'],
            'price' => (int) $row['price'],
            'image' => $row['image_webp'],
            'avif' => $row['image_avif'],
            'description' => $row['description'],
            'featured' => (bool) $row['is_featured'],
            'isNew' => (bool) $row['is_new'],
            'stockStatus' => $row['stock_status'],
        ];
    }
}
