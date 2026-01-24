<?php

namespace Stu\Lib\Pirate\Behaviour;

use RuntimeException;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class CallForSupportBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private readonly ShipRepositoryInterface $shipRepository,
        private readonly PirateCreationInterface $pirateCreation,
        private readonly DistanceCalculationInterface $distanceCalculation,
        private readonly ReloadMinimalEpsInterface $reloadMinimalEps,
        private readonly PirateNavigationInterface $pirateNavigation,
        private readonly BattlePartyFactoryInterface $battlePartyFactory,
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function action(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $leadWrapper = $pirateFleetBattleParty->getLeader();

        $supportFleet = $this->getSupportFleet($leadWrapper, $reactionMetadata);

        if ($supportFleet === null) {
            return PirateBehaviourEnum::SEARCH_FRIEND;
        }

        $pirateReaction->react(
            $supportFleet,
            PirateReactionTriggerEnum::ON_SUPPORT_CALL,
            $leadWrapper->get(),
            $reactionMetadata
        );

        return null;
    }

    private function getSupportFleet(SpacecraftWrapperInterface $leadWrapper, PirateReactionMetadata $reactionMetadata): ?PirateFleetBattleParty
    {
        $leadSpacecraft = $leadWrapper->get();
        $friends = $this->shipRepository->getPirateFriends($leadWrapper);

        $filteredFriends = array_filter(
            $friends,
            fn(Spacecraft $friend): bool =>
            !$friend->getCondition()->isDestroyed()
                && $friend->isFleetLeader()
                && $friend->getLocation() !== $leadSpacecraft->getLocation()
        );

        usort(
            $filteredFriends,
            fn(Spacecraft $a, Spacecraft $b): int =>
            $this->distanceCalculation->spacecraftToSpacecraftDistance($leadSpacecraft, $a) - $this->distanceCalculation->spacecraftToSpacecraftDistance($leadSpacecraft, $b)
        );


        $closestFriend = current($filteredFriends);
        if (!$closestFriend) {
            return $this->createSupportFleet($leadSpacecraft, $reactionMetadata);
        }

        $supportFleet = $closestFriend->getFleet();
        if ($supportFleet === null) {
            throw new RuntimeException('pirate ships should always be in fleet');
        }

        $this->logger->logf(
            '    calling already existing support fleet %s (%d) to %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $leadSpacecraft->getSectorString()
        );

        $supportPirateFleetBattleParty = $this->battlePartyFactory->createPirateFleetBattleParty($supportFleet);

        $this->reloadMinimalEps->reload($supportPirateFleetBattleParty, 75);
        if (!$this->pirateNavigation->navigateToTarget($supportPirateFleetBattleParty, $leadSpacecraft->getLocation())) {
            return $this->createSupportFleet($leadSpacecraft, $reactionMetadata);
        }

        $this->logger->logf(
            '    already existing support fleet (%d) "%s" reached here %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $supportFleet->getLeadShip()->getSectorString()
        );

        return $supportPirateFleetBattleParty;
    }

    private function createSupportFleet(Spacecraft $leadSpacecraft, PirateReactionMetadata $reactionMetadata): ?PirateFleetBattleParty
    {
        if (!$this->isNewSupportEligible($reactionMetadata)) {
            $this->logger->log('....support creation not eligible');
            return null;
        }

        $supportFleet = $this->pirateCreation->createPirateFleet($leadSpacecraft);
        $this->logger->logf(
            '    created support fleet %d "%s" here %s',
            $supportFleet->getId(),
            $supportFleet->getName(),
            $supportFleet->getLeadShip()->getSectorString()
        );

        return $this->battlePartyFactory->createPirateFleetBattleParty($supportFleet);
    }


    private function isNewSupportEligible(PirateReactionMetadata $reactionMetadata): bool
    {
        $supportCallAmount = $reactionMetadata->getReactionAmount(PirateBehaviourEnum::CALL_FOR_SUPPORT);

        if ($supportCallAmount <= 1) {
            $currentPirateFleetAmount = $this->fleetRepository->getCountByUser(UserConstants::USER_NPC_KAZON);

            $this->logger->logf(
                '....supportCallAmount: %d, currentPirateFleetAmount: %d',
                $supportCallAmount,
                $currentPirateFleetAmount
            );

            return $this->stuRandom->rand(1, max(1, $currentPirateFleetAmount)) === 1;
        }

        return true;
    }
}
