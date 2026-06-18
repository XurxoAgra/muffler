<?php

declare(strict_types=1);

namespace App\Auth\Domain\Event;

use App\Auth\Domain\User\UserId;
use App\Shared\Domain\Event\DomainEvent;

final class UserLoggedIn implements DomainEvent
{
    public function __construct(
        public readonly UserId $userId,
        public readonly \DateTimeImmutable $occurredOn,
    ) {
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
