<?php

declare(strict_types=1);

namespace Nova\Repository;

use PDO;
use Throwable;

final class AdminOrderRepository
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
            $where[] = '(order_number LIKE :number OR customer_name LIKE :name OR customer_phone LIKE :phone)';
            $value = '%' . $term . '%';
            $parameters = ['number' => $value, 'name' => $value, 'phone' => $value];
        }
        if ($status !== null) {
            $where[] = 'status = :status';
            $parameters['status'] = $status;
        }
        $sqlWhere = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $statement = $this->database->prepare(
            'SELECT id, order_number, customer_name, customer_phone, total_amount, total_quantity, status, created_at
             FROM orders' . $sqlWhere . ' ORDER BY id DESC LIMIT 200'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $order = $statement->fetch();
        if ($order === false) {
            return null;
        }

        $items = $this->database->prepare(
            'SELECT product_id, product_name, unit_price, quantity, line_total
             FROM order_items WHERE order_id = :id ORDER BY id'
        );
        $items->execute(['id' => $id]);
        $order['items'] = $items->fetchAll();

        $history = $this->database->prepare(
            'SELECT h.previous_status, h.new_status, h.created_at, a.username
             FROM order_status_history h INNER JOIN admins a ON a.id = h.admin_id
             WHERE h.order_id = :id ORDER BY h.id DESC'
        );
        $history->execute(['id' => $id]);
        $order['history'] = $history->fetchAll();

        return $order;
    }

    public function updateStatus(int $orderId, string $newStatus, int $adminId): bool
    {
        try {
            $this->database->beginTransaction();
            $statement = $this->database->prepare('SELECT status FROM orders WHERE id = :id FOR UPDATE');
            $statement->execute(['id' => $orderId]);
            $previousStatus = $statement->fetchColumn();
            if ($previousStatus === false) {
                $this->database->rollBack();
                return false;
            }
            if ($previousStatus === $newStatus) {
                $this->database->commit();
                return true;
            }

            $update = $this->database->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $update->execute(['status' => $newStatus, 'id' => $orderId]);
            $history = $this->database->prepare(
                'INSERT INTO order_status_history (order_id, admin_id, previous_status, new_status)
                 VALUES (:order_id, :admin_id, :previous_status, :new_status)'
            );
            $history->execute([
                'order_id' => $orderId,
                'admin_id' => $adminId,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
            ]);
            $this->database->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }
}
