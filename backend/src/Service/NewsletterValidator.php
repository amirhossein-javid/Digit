<?php

declare(strict_types=1);

namespace Nova\Service;

use Nova\Exception\ApiException;

final class NewsletterValidator
{
    public function validate(mixed $value): string
    {
        if (!is_string($value)) {
            throw new ApiException('invalid_email', 'نشانی ایمیل الزامی است.', 422);
        }
        $email = strtolower(trim($value));
        if ($email === '' || strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ApiException('invalid_email', 'نشانی ایمیل معتبر نیست.', 422);
        }
        return $email;
    }
}
