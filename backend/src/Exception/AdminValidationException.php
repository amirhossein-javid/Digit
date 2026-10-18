<?php

declare(strict_types=1);

namespace Nova\Exception;

final class AdminValidationException extends \RuntimeException
{
    /** @param list<string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct($errors[0] ?? 'اطلاعات واردشده معتبر نیست.');
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
