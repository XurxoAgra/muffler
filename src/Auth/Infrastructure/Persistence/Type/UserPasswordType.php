<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Type;

use App\Auth\Domain\User\UserPassword;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class UserPasswordType extends Type
{
    public const NAME = 'user_password';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 255] + $column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserPassword
    {
        return $value !== null ? UserPassword::fromHash($value) : null;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return $value instanceof UserPassword ? $value->hashedValue() : $value;
    }
}
