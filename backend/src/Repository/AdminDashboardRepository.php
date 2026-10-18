<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;

final class AdminDashboardRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /** @return array{products: int, orders: int, pendingOrders: int} */
    public function counts(): array
    {
        return [
            'products' => (int) $this->database->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
            'orders' => (int) $this->database->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'pendingOrders' => (int) $this->database->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function recentOrders(int $limit = 8): array
    {
        $statement = $this->database->prepare(
            'SELECT id, order_number, customer_name, total_amount, total_quantity, status, created_at
             FROM orders ORDER BY id DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
