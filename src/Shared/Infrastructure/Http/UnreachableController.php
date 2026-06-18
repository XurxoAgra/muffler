<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

// Bound to routes that exist only so the router doesn't 404 before a
// security firewall (json_login, refresh-jwt) intercepts the request.
// Reaching this controller means the firewall failed to do so.
final class UnreachableController
{
    public function __invoke(): never
    {
        throw new \LogicException('This route should have been intercepted by the security firewall.');
    }
}
