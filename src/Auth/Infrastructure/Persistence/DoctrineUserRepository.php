<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserEmail;
use App\Auth\Domain\User\UserId;
use App\Auth\Domain\User\UserPassword;
use App\Auth\Domain\User\UserRepository;
use App\Auth\Domain\User\UserRole;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function save(User $user): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM users WHERE id = ?',
            [$user->id()->value()],
        );

        $data = [
            'id' => $user->id()->value(),
            'email' => $user->email()->value(),
            'password' => $user->password()->hashedValue(),
            'first_name' => $user->firstName(),
            'last_name' => $user->lastName(),
            'roles' => json_encode(array_map(fn (UserRole $r) => $r->value, $user->roles())),
            'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->updatedAt()->format('Y-m-d H:i:s'),
        ];

        if ($exists) {
            $this->connection->update('users', $data, ['id' => $user->id()->value()]);

            return;
        }

        $this->connection->insert('users', $data);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function findById(UserId $id): ?User
    {
        $user = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$id->value()],
        );

        return $user !== false ? $this->hydrate($user) : null;
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function findByEmail(UserEmail $email): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE email = ?',
            [$email->value()],
        );

        return $row !== false ? $this->hydrate($row) : null;
    }

    public function nextId(): UserId
    {
        return UserId::fromString(Uuid::v7()->toRfc4122());
    }

    /**
     * @throws \Exception
     */
    private function hydrate(array $user): User
    {
        return User::reconstitute(
            id: UserId::fromString($user['id']),
            email: new UserEmail($user['email']),
            password: UserPassword::fromHash($user['password']),
            firstName: $user['first_name'],
            lastName: $user['last_name'],
            roles: array_map(
                fn (string $rol) => UserRole::fromString($rol),
                json_decode($user['roles'], true),
            ),
            createdAt: new \DateTimeImmutable($user['created_at']),
            updatedAt: new \DateTimeImmutable($user['updated_at']),
        );
    }
}
