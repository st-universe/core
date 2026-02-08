<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

class AlertedShipsDetection implements AlertedShipsDetectionInterface
{
    public function __construct(
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[\Override]
    public function getAlertedShipsOnLocation(
        Location $location,
        User $user
    ): Collection {
        return $location->getSpacecraftsWithoutVacation()
            ->filter(
                fn (Spacecraft $spacecraft): bool =>
                $spacecraft->getUser()->getId() !== $user->getId()
                    && ($spacecraft->getFleet() === null || !$spacecraft instanceof Ship || $spacecraft->isFleetLeader())
                    && !$spacecraft->isWarped()
                    && !$spacecraft->isCloaked()
            )
            ->map(fn (Spacecraft $spacecraft): SpacecraftWrapperInterface => $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft))
            ->filter(fn (SpacecraftWrapperInterface $wrapper): bool => !$wrapper->isUnalerted());
    }
}
