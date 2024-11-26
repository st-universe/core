<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use RuntimeException;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Deletes ship buildplans on user deletion
 */
final class ShipBuildplanDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->shipBuildplanRepository->getByUser($user->getId()) as $shipBuildplan) {

            $existsForeignShip = !$shipBuildplan->getShiplist()
                ->filter(fn(ShipInterface $ship): bool => $ship->getUser() !== $shipBuildplan->getUser())
                ->isEmpty();

            if ($existsForeignShip) {
                $user = $this->userRepository->find(UserEnum::USER_FOREIGN_BUILDPLANS);
                if ($user === null) {
                    throw new RuntimeException(sprintf('user with id %d does not exist', UserEnum::USER_FOREIGN_BUILDPLANS));
                }
                $shipBuildplan->setUser($user);
                $this->shipBuildplanRepository->save($shipBuildplan);
            } else {
                $this->shipBuildplanRepository->delete($shipBuildplan);
            }
        }
    }
}
