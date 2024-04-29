<?php

namespace Stu\Lib\Pirate\Behaviour;

use RuntimeException;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class CallForSupportBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private PirateCreationInterface $pirateCreation,
        private DistanceCalculationInterface $distanceCalculation,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        private PirateNavigationInterface $pirateNavigation,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $supportFleet = $this->getSupportFleet($leadShip);

        $pirateReaction->react(
            $supportFleet,
            PirateReactionTriggerEnum::ON_SUPPORT_CALL
        );

        return null;
    }

    private function getSupportFleet(ShipInterface $leadShip): FleetInterface
    {
        $friends = $this->shipRepository->getPirateFriends($leadShip);

        usort(
            $friends,
            fn (ShipInterface $a, ShipInterface $b) =>
            $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b)
        );

        $closestFriend = current($friends);
        if (!$closestFriend) {
            return $this->createSupportFleet($leadShip);
        }

        $friendFleet = $closestFriend->getFleet();
        if ($friendFleet === null) {
            throw new RuntimeException('pirate ships should always be in fleet');
        }

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($friendFleet);

        $this->reloadMinimalEps->reload($fleetWrapper, 75);
        $this->pirateNavigation->navigateToTarget($fleetWrapper, $leadShip->getCurrentMapField());

        return $friendFleet;
    }

    private function createSupportFleet(ShipInterface $leadShip): FleetInterface
    {
        $supportFleet = $this->pirateCreation->createPirateFleet($leadShip);
        $this->logger->logf(
            '    created support fleet %d "%s" here %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $supportFleet->getLeadShip()->getSectorString()
        );

        return $supportFleet;
    }
}
