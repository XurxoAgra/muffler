<?php

declare(strict_types=1);

namespace App\Auth\Application\VerifyEmail;

use App\Auth\Application\Port\VerificationTokenSigner;
use App\Auth\Domain\User\Exception\InvalidVerificationTokenException;
use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VerifyEmailHandler
{
    public function __construct(
        private UserRepository $users,
        private VerificationTokenSigner $signer,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(VerifyEmailCommand $command): void
    {
        $user = $this->users->findById($this->signer->extractUserId($command->token));

        if (null === $user) {
            throw new InvalidVerificationTokenException();
        }

        $this->signer->assertValidFor($command->token, $user);

        if ($user->isVerified()) {
            return;
        }

        $user->verify();

        $this->users->save($user);
        $this->dispatchEvents($user);
    }

    private function dispatchEvents(User $user): void
    {
        foreach ($user->pullEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
