<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\User\UserRole;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class UserRolesType extends Type
{
    public const NAME = 'user_roles';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /** @return UserRole[] */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if (null === $value) {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return array_map(static fn (string $role) => UserRole::fromString($role), $decoded);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        $roles = is_array($value) ? $value : [];

        return json_encode(array_map(static fn (UserRole $role) => $role->value, $roles));
    }
}
