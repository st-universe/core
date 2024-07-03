<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Lib;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;

/**
 * Registers a player's profile visit by another user
 */
final class ProfileVisitorRegistration implements ProfileVisitorRegistrationInterface
{
    public function __construct(private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository)
    {
    }

    #[Override]
    public function register(
        UserInterface $user,
        UserInterface $visitor
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
