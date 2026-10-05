<?php

declare(strict_types=1);

namespace App\Auth\Domain\User\Exception;

use App\Auth\Domain\Exception\DomainException;

final class VerificationTokenExpiredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The verification link has expired');
    }
}
