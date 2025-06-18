<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

class AlertedShipsDetection implements AlertedShipsDetectionInterface
{
    public function __construct(
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function getAlertedShipsOnLocation(
        LocationInterface $location,
        UserInterface $user
    ): Collection {
        return $location->getSpacecraftsWithoutVacation()
            ->filter(
                fn(SpacecraftInterface $spacecraft): bool =>
                $spacecraft->getUser() !== $user
                    && ($spacecraft->getFleet() === null || !$spacecraft instanceof ShipInterface || $spacecraft->isFleetLeader())
                    && !$spacecraft->isAlertGreen()
                    && !$spacecraft->isWarped()
                    && !$spacecraft->isCloaked()
            )
            ->map(fn(SpacecraftInterface $spacecraft): SpacecraftWrapperInterface => $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft));
    }
}
