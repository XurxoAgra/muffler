<?php

declare(strict_types=1);

namespace App\Auth\Domain\User\Exception;

use App\Auth\Domain\Exception\DomainException;

final class UserNotFoundException extends DomainException
{
}
