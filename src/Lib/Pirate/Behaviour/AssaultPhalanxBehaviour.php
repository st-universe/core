<?php

namespace Stu\Lib\Pirate\Behaviour;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\TrapDetectionInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\BoardShipUtilInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\CloseCombatUtilInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

class AssaultPhalanxBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private StationRepositoryInterface $stationRepository,
        private DistanceCalculationInterface $distanceCalculation,
        private PirateNavigationInterface $pirateNavigation,
        private SpacecraftAttackCoreInterface $spacecraftAttackCore,
        private CloseCombatUtilInterface $closeCombatUtil,
        private BoardShipUtilInterface $boardShip,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private TrapDetectionInterface $trapDetection,
        private MessageFactoryInterface $messageFactory,
        private DistributedMessageSenderInterface $distributedMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?SpacecraftInterface $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $targets = $this->stationRepository->getPiratePhalanxTargets($leadWrapper);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn(StationInterface $target): bool =>
            !$this->trapDetection->isAlertTrap($target->getLocation(), $leadShip)
        );

        $this->logger->log(sprintf('    %d filtered targets in reach', count($filteredTargets)));

        if ($filteredTargets === []) {
            return null;
        }

        usort(
            $filteredTargets,
            fn(StationInterface $a, StationInterface $b): int =>
            $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b)
        );

        $isFleetFight = false;
        $informations = new InformationWrapper();

        /** @var StationInterface */
        $closestPhalanx = current($filteredTargets);
        $phalanxWrapper = $this->spacecraftWrapperFactory->wrapStation($closestPhalanx);

        if (!$this->pirateNavigation->navigateToTarget($fleet, $closestPhalanx->getLocation())) {
            return null;
        }

        // take down shields only
        while ($closestPhalanx->isShielded()) {
            $this->spacecraftAttackCore->attack($leadWrapper, $phalanxWrapper, true, $isFleetFight, $informations);
        }

        if ($closestPhalanx->isDestroyed()) {
            return null;
        }

        $boardingWrapper = $this->getBoardingPirateWrapper($fleet);
        $boardingShip = $boardingWrapper->get();

        $this->spacecraftSystemManager->deactivate($boardingWrapper, SpacecraftSystemTypeEnum::SHIELDS, true);

        $combatGroupAttacker = $this->closeCombatUtil->getCombatGroup($boardingShip);
        $combatGroupDefender = $this->closeCombatUtil->getCombatGroup($closestPhalanx);

        $messages = $this->messageFactory->createMessageCollection();
        $message = $this->messageFactory->createMessage(UserEnum::USER_NPC_KAZON, $closestPhalanx->getUser()->getId(), [sprintf(
            'Das Piratenschiff %s entsendet ein Enterkommando auf die %s',
            $boardingShip->getName(),
            $closestPhalanx->getName()
        )]);

        $messages->add($message);

        while ($combatGroupAttacker !== [] && $combatGroupDefender !== []) {

            $this->logger->logf('    %d vs %d', count($combatGroupAttacker), count($combatGroupDefender));

            $this->boardShip->cycleKillRound(
                $combatGroupAttacker,
                $combatGroupDefender,
                $boardingWrapper,
                $phalanxWrapper,
                $messages
            );
        }

        $this->sendPms(
            $closestPhalanx->getSectorString(),
            $messages
        );

        return null;
    }

    private function getBoardingPirateWrapper(FleetWrapperInterface $fleetWrapper): ShipWrapperInterface
    {

        $pirateWrapperArray = $fleetWrapper->getShipWrappers()->toArray();

        usort(
            $pirateWrapperArray,
            fn(ShipWrapperInterface $a, ShipWrapperInterface $b): int =>
            $b->get()->getCrewCount() - $a->get()->getCrewCount()
        );

        $pirateShipWithMostCrew = current($pirateWrapperArray);
        if ($pirateShipWithMostCrew === false) {
            throw new RuntimeException('this should not happen');
        }

        return $pirateShipWithMostCrew;
    }

    private function sendPms(
        string $sectorString,
        MessageCollectionInterface $messageCollection
    ): void {

        $header = sprintf(
            "Plünderung in Sektor %s",
            $sectorString
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollection,
            UserEnum::USER_NPC_KAZON,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $header,
            true
        );
    }
}
