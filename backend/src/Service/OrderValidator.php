<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Exception\ApiException;

final class OrderValidator
{
    private const MAX_ITEMS = 50;
    private const MAX_QUANTITY_PER_PRODUCT = 20;
    private const MAX_TOTAL_QUANTITY = 100;

    /**
     * @param array<string, mixed> $payload
     * @return array{customer: array<string, string|null>, items: list<array{product_id: int, quantity: int}>, idempotency_key: string}
     */
    public function validate(array $payload, ?string $idempotencyKey): array
    {
        if ($idempotencyKey === null || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $idempotencyKey)) {
            throw new ApiException('invalid_idempotency_key', 'شناسه یکتای درخواست معتبر نیست.', 422);
        }

        $customer = $payload['customer'] ?? null;
        if (!is_array($customer) || array_is_list($customer)) {
            throw new ApiException('invalid_customer', 'اطلاعات دریافت‌کننده کامل نیست.', 422);
        }

        $name = $this->requiredText($customer, 'name', 3, 100, 'نام و نام خانوادگی');
        $phone = $this->normalizeDigits($this->requiredText($customer, 'phone', 11, 30, 'شماره موبایل'));
        $phone = preg_replace('/[\s\-()]+/u', '', $phone) ?? $phone;
        if (!preg_match('/^09\d{9}$/', $phone)) {
            throw new ApiException('invalid_phone', 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم داشته باشد.', 422);
        }

        $email = $this->optionalText($customer, 'email', 190);
        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ApiException('invalid_email', 'نشانی ایمیل معتبر نیست.', 422);
        }

        $city = $this->requiredText($customer, 'city', 2, 100, 'شهر');
        $address = $this->requiredText($customer, 'address', 10, 500, 'نشانی');
        $postalCode = $this->optionalText($customer, 'postalCode', 20);
        if ($postalCode !== null) {
            $postalCode = $this->normalizeDigits(preg_replace('/\s+/u', '', $postalCode) ?? $postalCode);
            if (!preg_match('/^\d{10}$/', $postalCode)) {
                throw new ApiException('invalid_postal_code', 'کد پستی باید ۱۰ رقمی باشد.', 422);
            }
        }

        $items = $payload['items'] ?? null;
        if (!is_array($items) || $items === [] || count($items) > self::MAX_ITEMS) {
            throw new ApiException('invalid_items', 'سبد خرید خالی یا تعداد اقلام آن نامعتبر است.', 422);
        }

        $quantities = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new ApiException('invalid_items', 'اطلاعات یکی از اقلام سفارش نامعتبر است.', 422);
            }

            $productId = filter_var($item['productId'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => self::MAX_QUANTITY_PER_PRODUCT],
            ]);

            if ($productId === false || $quantity === false) {
                throw new ApiException('invalid_items', 'شناسه محصول یا تعداد سفارش نامعتبر است.', 422);
            }

            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
            if ($quantities[$productId] > self::MAX_QUANTITY_PER_PRODUCT) {
                throw new ApiException('invalid_quantity', 'تعداد مجاز هر محصول حداکثر ۲۰ عدد است.', 422);
            }
        }

        if (array_sum($quantities) > self::MAX_TOTAL_QUANTITY) {
            throw new ApiException('invalid_quantity', 'تعداد کل محصولات سفارش بیش از حد مجاز است.', 422);
        }

        $normalizedItems = [];
        foreach ($quantities as $productId => $quantity) {
            $normalizedItems[] = ['product_id' => (int) $productId, 'quantity' => $quantity];
        }

        return [
            'customer' => [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'city' => $city,
                'address' => $address,
                'postal_code' => $postalCode,
            ],
            'items' => $normalizedItems,
            'idempotency_key' => strtolower($idempotencyKey),
        ];
    }

    /** @param array<string, mixed> $values */
    private function requiredText(array $values, string $key, int $minimum, int $maximum, string $label): string
    {
        $value = $values[$key] ?? null;
        if (!is_string($value)) {
            throw new ApiException('invalid_customer', sprintf('فیلد «%s» الزامی است.', $label), 422);
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        $length = mb_strlen($value, 'UTF-8');
        if ($length < $minimum || $length > $maximum) {
            throw new ApiException('invalid_customer', sprintf('مقدار فیلد «%s» معتبر نیست.', $label), 422);
        }

        return $value;
    }

    /** @param array<string, mixed> $values */
    private function optionalText(array $values, string $key, int $maximum): ?string
    {
        $value = $values[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new ApiException('invalid_customer', 'یکی از فیلدهای اختیاری نامعتبر است.', 422);
        }

        $value = trim($value);
        if ($value === '' || mb_strlen($value, 'UTF-8') > $maximum) {
            throw new ApiException('invalid_customer', 'یکی از فیلدهای اختیاری نامعتبر است.', 422);
        }

        return $value;
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
