<?php

declare(strict_types=1);

namespace App\Shared\Domain\Aggregate;

abstract class AggregateRoot
{
    private array $domainEvents = [];

    final protected function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /** @return object[] */
    final public function pullEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
