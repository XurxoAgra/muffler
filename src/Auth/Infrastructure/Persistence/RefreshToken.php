<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use Gesdinet\JWTRefreshTokenBundle\Model\AbstractRefreshToken;

// Doctrine ORM entity required by GesdinetJWTRefreshTokenBundle to persist
// refresh tokens — mapped via XML (see Mapping/RefreshToken.orm.xml).
final class RefreshToken extends AbstractRefreshToken
{
}
