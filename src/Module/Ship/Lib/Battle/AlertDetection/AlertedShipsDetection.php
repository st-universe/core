<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

class AlertedShipsDetection implements AlertedShipsDetectionInterface
{
    public function __construct(
        private ShipWrapperFactoryInterface $shipWrapperFactory
    ) {}

    #[Override]
    public function getAlertedShipsOnLocation(
        LocationInterface $location,
        UserInterface $user
    ): Collection {
        return $location->getShips()
            ->filter(
                fn(ShipInterface $ship): bool => !$ship->getUser()->isVacationRequestOldEnough()
                    && $ship->getUser() !== $user
                    && ($ship->isFleetLeader() || $ship->getFleet() === null)
                    && !$ship->isAlertGreen()
                    && !$ship->isWarped()
                    && !$ship->getCloakState()
            )
            ->map(fn(ShipInterface $ship): ShipWrapperInterface => $this->shipWrapperFactory->wrapShip($ship));
    }
}
