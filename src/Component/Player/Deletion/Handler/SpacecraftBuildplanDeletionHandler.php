<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use RuntimeException;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
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

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->spacecraftBuildplanRepository->getByUser($user->getId()) as $spacecraftBuildplan) {

            $existsForeignShip = !$spacecraftBuildplan->getShiplist()
                ->filter(fn(ShipInterface $ship): bool => $ship->getUser() !== $spacecraftBuildplan->getUser())
                ->isEmpty();

            if ($existsForeignShip) {
                $user = $this->userRepository->find(UserEnum::USER_FOREIGN_BUILDPLANS);
                if ($user === null) {
                    throw new RuntimeException(sprintf('user with id %d does not exist', UserEnum::USER_FOREIGN_BUILDPLANS));
                }
                $spacecraftBuildplan->setUser($user);
                $this->spacecraftBuildplanRepository->save($spacecraftBuildplan);
            } else {
                $this->spacecraftBuildplanRepository->delete($spacecraftBuildplan);
            }
        }
    }
}
