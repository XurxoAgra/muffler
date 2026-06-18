<?php

declare(strict_types=1);

namespace App\Shared\Application\Exception;

final class ValidationException extends \RuntimeException
{
    public function __construct(string $message, private readonly array $details)
    {
        parent::__construct($message);
    }

    public function details(): array
    {
        return $this->details;
    }
}
