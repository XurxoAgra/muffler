<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\User\UserEmail;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class UserEmailType extends Type
{
    public const NAME = 'user_email';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 180] + $column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserEmail
    {
        return null !== $value ? new UserEmail($value) : null;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value instanceof UserEmail ? $value->value() : $value;
    }
}
