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
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
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
        private FleetRepositoryInterface $fleetRepository,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum {

        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $supportFleet = $this->getSupportFleet($leadShip, $reactionMetadata);

        if ($supportFleet === null) {
            return PirateBehaviourEnum::SEARCH_FRIEND;
        }

        $pirateReaction->react(
            $supportFleet,
            PirateReactionTriggerEnum::ON_SUPPORT_CALL,
            $leadShip,
            $reactionMetadata
        );

        return null;
    }

    private function getSupportFleet(ShipInterface $leadShip, PirateReactionMetadata $reactionMetadata): ?FleetInterface
    {
        $friends = $this->shipRepository->getPirateFriends($leadShip);

        $filteredFriends = array_filter(
            $friends,
            fn (ShipInterface $friend) =>
            !$friend->isDestroyed()
                && $friend->isFleetLeader()
                && $friend->getCurrentMapField() !== $leadShip->getCurrentMapField()
        );

        usort(
            $filteredFriends,
            fn (ShipInterface $a, ShipInterface $b) =>
            $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b)
        );


        $closestFriend = current($filteredFriends);
        if (!$closestFriend) {
            return $this->createSupportFleet($leadShip, $reactionMetadata);
        }

        $supportFleet = $closestFriend->getFleet();
        if ($supportFleet === null) {
            throw new RuntimeException('pirate ships should always be in fleet');
        }

        $this->logger->logf(
            '    calling already existing support fleet %s (%d) to %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $leadShip->getSectorString()
        );

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($supportFleet);

        $this->reloadMinimalEps->reload($fleetWrapper, 75);
        if (!$this->pirateNavigation->navigateToTarget($fleetWrapper, $leadShip->getCurrentMapField())) {
            return $this->createSupportFleet($leadShip, $reactionMetadata);
        }

        $this->logger->logf(
            '    already existing support fleet (%d) "%s" reached here %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $supportFleet->getLeadShip()->getSectorString()
        );

        return $supportFleet;
    }

    private function createSupportFleet(ShipInterface $leadShip, PirateReactionMetadata $reactionMetadata): ?FleetInterface
    {
        if (!$this->isNewSupportEligible($reactionMetadata)) {
            return null;
        }

        $supportFleet = $this->pirateCreation->createPirateFleet($leadShip);
        $this->logger->logf(
            '    created support fleet %d "%s" here %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $supportFleet->getLeadShip()->getSectorString()
        );

        return $supportFleet;
    }


    private function isNewSupportEligible(PirateReactionMetadata $reactionMetadata): bool
    {
        $supportCallAmount = $reactionMetadata->getReactionAmount(PirateBehaviourEnum::CALL_FOR_SUPPORT);

        if ($supportCallAmount <= 1) {
            $currentPirateFleetAmount = $this->fleetRepository->getCountByUser(UserEnum::USER_NPC_KAZON);

            return $this->stuRandom->rand(1, max(1, $currentPirateFleetAmount)) == 1;
        }

        return true;
    }
}
