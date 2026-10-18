<?php

declare(strict_types=1);

namespace Nova\Repository;

use Nova\Exception\ApiException;
use PDO;
use PDOException;
use Throwable;

final class OrderRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    /**
     * @param array{customer: array<string, string|null>, items: list<array{product_id: int, quantity: int}>, idempotency_key: string} $order
     * @return array{orderNumber: string, total: int, itemCount: int, createdAt: string, duplicate: bool}
     */
    public function create(array $order): array
    {
        $existing = $this->findByIdempotencyKey($order['idempotency_key']);
        if ($existing !== null) {
            return $this->mapResult($existing, true);
        }

        try {
            $this->database->beginTransaction();

            $products = $this->lockProducts(array_column($order['items'], 'product_id'));
            if (count($products) !== count($order['items'])) {
                throw new ApiException('product_not_found', 'یکی از محصولات سفارش در دسترس نیست.', 422);
            }

            $lines = [];
            $total = 0;
            $totalQuantity = 0;
            foreach ($order['items'] as $item) {
                $product = $products[$item['product_id']];
                if ($product['stock_status'] === 'out_of_stock') {
                    throw new ApiException('product_unavailable', sprintf('محصول «%s» در حال حاضر موجود نیست.', $product['name']), 409);
                }

                $unitPrice = (int) $product['price'];
                $quantity = $item['quantity'];
                if ($unitPrice > intdiv(PHP_INT_MAX, $quantity)) {
                    throw new \RuntimeException('Order line total exceeds the supported integer range.');
                }

                $lineTotal = $unitPrice * $quantity;
                if ($total > PHP_INT_MAX - $lineTotal) {
                    throw new \RuntimeException('Order total exceeds the supported integer range.');
                }

                $total += $lineTotal;
                $totalQuantity += $quantity;
                $lines[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product['name'],
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }

            $orderNumber = $this->generateOrderNumber();
            $customer = $order['customer'];
            $orderStatement = $this->database->prepare(
                'INSERT INTO orders
                    (order_number, idempotency_key, customer_name, customer_phone, customer_email,
                     city, address, postal_code, status, total_amount, total_quantity)
                 VALUES
                    (:order_number, :idempotency_key, :customer_name, :customer_phone, :customer_email,
                     :city, :address, :postal_code, :status, :total_amount, :total_quantity)'
            );
            $orderStatement->execute([
                'order_number' => $orderNumber,
                'idempotency_key' => $order['idempotency_key'],
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'],
                'city' => $customer['city'],
                'address' => $customer['address'],
                'postal_code' => $customer['postal_code'],
                'status' => 'pending',
                'total_amount' => $total,
                'total_quantity' => $totalQuantity,
            ]);
            $orderId = (int) $this->database->lastInsertId();

            $itemStatement = $this->database->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, product_name, unit_price, quantity, line_total)
                 VALUES
                    (:order_id, :product_id, :product_name, :unit_price, :quantity, :line_total)'
            );
            foreach ($lines as $line) {
                $itemStatement->execute(['order_id' => $orderId] + $line);
            }

            $this->database->commit();
            $saved = $this->findByIdempotencyKey($order['idempotency_key']);
            if ($saved === null) {
                throw new \RuntimeException('The saved order could not be loaded.');
            }

            return $this->mapResult($saved, false);
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }

            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                $existing = $this->findByIdempotencyKey($order['idempotency_key']);
                if ($existing !== null) {
                    return $this->mapResult($existing, true);
                }
            }

            throw $exception;
        }
    }

    /** @return array<string, mixed>|null */
    private function findByIdempotencyKey(string $key): ?array
    {
        $statement = $this->database->prepare(
            'SELECT order_number, total_amount, total_quantity, created_at
             FROM orders
             WHERE idempotency_key = :idempotency_key
             LIMIT 1'
        );
        $statement->execute(['idempotency_key' => $key]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @param list<int> $ids
     *  @return array<int, array<string, mixed>>
     */
    private function lockProducts(array $ids): array
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = $this->database->prepare(
            'SELECT id, name, price, stock_status
             FROM products
             WHERE id IN (' . $placeholders . ')
             FOR UPDATE'
        );
        foreach ($ids as $index => $id) {
            $statement->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
        $statement->execute();

        $products = [];
        foreach ($statement->fetchAll() as $product) {
            $products[(int) $product['id']] = $product;
        }

        return $products;
    }

    private function generateOrderNumber(): string
    {
        return 'NVA-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));
    }

    /** @param array<string, mixed> $row
     *  @return array{orderNumber: string, total: int, itemCount: int, createdAt: string, duplicate: bool}
     */
    private function mapResult(array $row, bool $duplicate): array
    {
        return [
            'orderNumber' => $row['order_number'],
            'total' => (int) $row['total_amount'],
            'itemCount' => (int) $row['total_quantity'],
            'createdAt' => $row['created_at'],
            'duplicate' => $duplicate,
        ];
    }
}
