<?php

declare(strict_types=1);

namespace App\Auth\Domain\User\Exception;

use App\Auth\Domain\Exception\DomainException;
use App\Auth\Domain\User\UserEmail;

final class UserAlreadyExistsException extends DomainException
{
    public function __construct(UserEmail $email)
    {
        parent::__construct("User with email {$email} already exists");
    }
}
