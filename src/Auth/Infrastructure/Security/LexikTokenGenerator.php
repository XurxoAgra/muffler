<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Token\TokenPair;
use App\Auth\Domain\User\User;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class LexikTokenGenerator implements TokenGenerator
{
    private const REFRESH_TOKEN_TTL = 2592000;

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenGeneratorInterface $refreshGenerator,
        private readonly RefreshTokenManagerInterface $refreshManager,
        private readonly UserSymfonyProvider $provider,
    ) {
    }

    public function generatePair(User $user): TokenPair
    {
        $symfonyUser = $this->provider->fromDomain($user);

        $accessToken = $this->jwtManager->createFromPayload($symfonyUser, [
            'email' => $user->email()->value(),
        ]);

        $refreshToken = $this->refreshGenerator->createForUserWithTtl($symfonyUser, self::REFRESH_TOKEN_TTL);
        $this->refreshManager->save($refreshToken);

        return new TokenPair($accessToken, $refreshToken->getRefreshToken());
    }

    public function generateAccessToken(User $user): string
    {
        return $this->jwtManager->createFromPayload(
            $this->provider->fromDomain($user),
            ['email' => $user->email()->value()],
        );
    }
}
