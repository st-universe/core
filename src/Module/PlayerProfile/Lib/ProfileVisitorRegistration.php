<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Lib;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;

/**
 * Registers a player's profile visit by another user
 */
final class ProfileVisitorRegistration implements ProfileVisitorRegistrationInterface
{
    public function __construct(private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository) {}

    #[\Override]
    public function register(
        User $user,
        User $visitor
    ): void {
        if (
            $user->getId() !== $visitor->getId() &&
            $this->userProfileVisitorRepository->isVisitRegistered($user, $visitor) === false
        ) {
            $obj = $this->userProfileVisitorRepository->prototype()
                ->setProfileUser($user)
                ->setUser($visitor)
                ->setDate(time());

            $this->userProfileVisitorRepository->save($obj);
        }
    }
}
