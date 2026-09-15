<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Security;

use App\Auth\Infrastructure\Security\SymfonyUserAdapter;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleUserRepository;
use App\Vehicle\Domain\VehicleUserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class VehicleVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const MANAGE_USERS = 'MANAGE_USERS';

    public function __construct(private readonly VehicleUserRepository $vehicleUsers)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Vehicle
            && in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::MANAGE_USERS], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof SymfonyUserAdapter) {
            return false;
        }

        /** @var Vehicle $vehicle */
        $vehicle = $subject;

        $link = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $user->userId);

        if (null === $link) {
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::EDIT => true,
            self::DELETE, self::MANAGE_USERS => VehicleUserRole::Owner === $link->getRole(),
            default => false,
        };
    }
}
