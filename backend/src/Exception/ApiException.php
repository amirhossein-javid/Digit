<?php

declare(strict_types=1);

namespace Nova\Exception;

final class ApiException extends \RuntimeException
{
    public function __construct(
        private readonly string $errorCode,
        string $message,
        private readonly int $status
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function status(): int
    {
        return $this->status;
    }
}
