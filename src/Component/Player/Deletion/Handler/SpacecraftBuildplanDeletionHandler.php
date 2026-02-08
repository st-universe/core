<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use InvalidArgumentException;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Deletes ship buildplans on user deletion
 */
final class SpacecraftBuildplanDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function delete(User $user): void
    {
        foreach ($this->spacecraftBuildplanRepository->getByUser($user->getId()) as $spacecraftBuildplan) {

            $existsForeignShip = !$spacecraftBuildplan->getSpacecraftList()
                ->filter(fn (Spacecraft $spacecraft): bool => $spacecraft->getUser()->getId() !== $spacecraftBuildplan->getUser()->getId())
                ->isEmpty();

            if ($existsForeignShip) {
                $user = $this->userRepository->find(UserConstants::USER_FOREIGN_BUILDPLANS);
                if ($user === null) {
                    throw new InvalidArgumentException(sprintf('user with id %d does not exist', UserConstants::USER_FOREIGN_BUILDPLANS));
                }
                $spacecraftBuildplan->setUser($user);
                $this->spacecraftBuildplanRepository->save($spacecraftBuildplan);
            } else {
                $this->spacecraftBuildplanRepository->delete($spacecraftBuildplan);
            }
        }
    }
}
