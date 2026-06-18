<?php

declare(strict_types=1);

namespace App\Auth\Application\Register;

use App\Auth\Application\Port\PasswordHasher;
use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Token\TokenPair;
use App\Auth\Domain\User\Exception\UserAlreadyExistsException;
use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserEmail;
use App\Auth\Domain\User\UserPassword;
use App\Auth\Domain\User\UserRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepository           $users,
        private PasswordHasher           $hasher,
        private TokenGenerator           $tokens,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(RegisterUserCommand $command): TokenPair
    {
        $email = new UserEmail($command->email);

        $this->guardEmailIsUnique($email);

        $user = User::register(
            id: $this->users->nextId(),
            email: $email,
            password: UserPassword::fromHash($this->hasher->hash($command->rawPassword)),
            firstName: $command->firstName,
            lastName: $command->lastName,
        );

        $this->users->save($user);
        $this->dispatchEvents($user);

        return $this->tokens->generatePair($user);
    }

    private function guardEmailIsUnique(UserEmail $email): void
    {
        if ($this->users->findByEmail($email) !== null) {
            throw new UserAlreadyExistsException($email);
        }
    }

    private function dispatchEvents(User $user): void
    {
        foreach ($user->pullEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
