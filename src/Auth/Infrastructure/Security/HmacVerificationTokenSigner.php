<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\VerificationTokenSigner;
use App\Auth\Domain\User\Exception\InvalidUserIdException;
use App\Auth\Domain\User\Exception\InvalidVerificationTokenException;
use App\Auth\Domain\User\Exception\VerificationTokenExpiredException;
use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserId;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// Token format: base64url(json{uid, exp}) "." base64url(HMAC-SHA256(uid|email|exp)).
// The email is signed but not embedded, so the link does not expose it and
// stops working as soon as the user's email changes.
final readonly class HmacVerificationTokenSigner implements VerificationTokenSigner
{
    private const ALGORITHM = 'sha256';

    public function __construct(
        #[Autowire('%kernel.secret%')]
        private string $secret,
        #[Autowire('%env(int:EMAIL_VERIFICATION_TTL)%')]
        private int $ttlSeconds,
    ) {
        if ('' === $secret) {
            throw new \LogicException('APP_SECRET must be set to sign email verification tokens');
        }
    }

    public function sign(User $user): string
    {
        $userId = $user->id()->value();
        $expiresAt = time() + $this->ttlSeconds;

        $payload = $this->encode(json_encode(['uid' => $userId, 'exp' => $expiresAt], JSON_THROW_ON_ERROR));

        return $payload.'.'.$this->signature($userId, $user->email()->value(), $expiresAt);
    }

    public function extractUserId(string $token): UserId
    {
        try {
            return UserId::fromString($this->parse($token)['uid']);
        } catch (InvalidUserIdException) {
            throw new InvalidVerificationTokenException();
        }
    }

    public function assertValidFor(string $token, User $user): void
    {
        $payload = $this->parse($token);
        [, $signature] = explode('.', $token, 2);

        $expected = $this->signature($payload['uid'], $user->email()->value(), $payload['exp']);

        if ($payload['uid'] !== $user->id()->value() || !hash_equals($expected, $signature)) {
            throw new InvalidVerificationTokenException();
        }

        if ($payload['exp'] < time()) {
            throw new VerificationTokenExpiredException();
        }
    }

    /**
     * @return array{uid: string, exp: int}
     */
    private function parse(string $token): array
    {
        $parts = explode('.', $token);

        if (2 !== count($parts)) {
            throw new InvalidVerificationTokenException();
        }

        $json = $this->decode($parts[0]);
        $payload = false !== $json ? json_decode($json, true) : null;

        if (!is_array($payload) || !is_string($payload['uid'] ?? null) || !is_int($payload['exp'] ?? null)) {
            throw new InvalidVerificationTokenException();
        }

        return ['uid' => $payload['uid'], 'exp' => $payload['exp']];
    }

    private function signature(string $userId, string $email, int $expiresAt): string
    {
        return $this->encode(hash_hmac(self::ALGORITHM, "{$userId}|{$email}|{$expiresAt}", $this->secret, true));
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decode(string $value): string|false
    {
        return base64_decode(strtr($value, '-_', '+/'), true);
    }
}
