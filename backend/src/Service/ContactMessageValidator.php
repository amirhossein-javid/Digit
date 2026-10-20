<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Exception\ApiException;

final class ContactMessageValidator
{
    private const SUBJECTS = ['product_guidance', 'product_specs', 'cart_support'];

    /** @param array<string, mixed> $payload
     *  @return array{name: string, email: string, subject: string, message: string}
     */
    public function validate(array $payload): array
    {
        if (isset($payload['website']) && $payload['website'] !== '') {
            throw new ApiException('invalid_submission', 'امکان ثبت این پیام وجود ندارد.', 422);
        }

        $name = $this->text($payload['name'] ?? null, 3, 100, 'نام و نام خانوادگی');
        $email = strtolower($this->text($payload['email'] ?? null, 3, 190, 'ایمیل'));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ApiException('invalid_email', 'نشانی ایمیل معتبر نیست.', 422);
        }

        $subject = is_string($payload['subject'] ?? null) ? $payload['subject'] : '';
        if (!in_array($subject, self::SUBJECTS, true)) {
            throw new ApiException('invalid_subject', 'موضوع پیام معتبر نیست.', 422);
        }

        return [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $this->text($payload['message'] ?? null, 15, 2000, 'متن پیام', false),
        ];
    }

    private function text(mixed $value, int $minimum, int $maximum, string $label, bool $collapseWhitespace = true): string
    {
        if (!is_string($value)) {
            throw new ApiException('invalid_field', sprintf('فیلد «%s» الزامی است.', $label), 422);
        }
        $value = trim($value);
        if ($collapseWhitespace) {
            $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        }
        $length = mb_strlen($value, 'UTF-8');
        if ($length < $minimum || $length > $maximum) {
            throw new ApiException('invalid_field', sprintf('مقدار فیلد «%s» معتبر نیست.', $label), 422);
        }
        return $value;
    }
}
