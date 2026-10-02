<?php

declare(strict_types=1);

namespace App\Auth\Domain\Event;

use App\Auth\Domain\User\UserId;
use App\Shared\Domain\Event\DomainEvent;

final readonly class UserWasVerified implements DomainEvent
{
    public function __construct(
        public UserId $userId,
        public \DateTimeImmutable $occurredOn,
    ) {
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
