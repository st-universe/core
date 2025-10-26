<?php

namespace Stu\Lib\Pirate\Behaviour;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
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
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\BoardShipUtilInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\CloseCombatUtilInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
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
        private TroopTransferUtilityInterface $troopTransferUtility,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $targets = $this->stationRepository->getPiratePhalanxTargets($leadWrapper);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn(Station $target): bool =>
            !$this->trapDetection->isAlertTrap($target->getLocation(), $leadShip)
        );

        $this->logger->log(sprintf('    %d filtered targets in reach', count($filteredTargets)));

        if ($filteredTargets === []) {
            return null;
        }

        usort(
            $filteredTargets,
            fn(Station $a, Station $b): int =>
            $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b)
        );

        $isFleetFight = false;
        $informations = new InformationWrapper();

        /** @var Station */
        $closestPhalanx = current($filteredTargets);
        $phalanxWrapper = $this->spacecraftWrapperFactory->wrapStation($closestPhalanx);

        if (!$this->pirateNavigation->navigateToTarget($fleet, $closestPhalanx->getLocation())) {
            return null;
        }

        // take down shields only
        while ($closestPhalanx->isShielded()) {
            $this->spacecraftAttackCore->attack($leadWrapper, $phalanxWrapper, true, $isFleetFight, $informations);
        }

        if ($closestPhalanx->getCondition()->isDestroyed()) {
            return null;
        }

        $boardingWrapper = $this->getBoardingPirateWrapper($fleet);
        $boardingShip = $boardingWrapper->get();

        $this->spacecraftSystemManager->deactivate($boardingWrapper, SpacecraftSystemTypeEnum::SHIELDS, true);

        $combatGroupAttacker = $this->closeCombatUtil->getCombatGroup($boardingShip);
        $combatGroupDefender = $this->closeCombatUtil->getCombatGroup($closestPhalanx);

        $messages = $this->messageFactory->createMessageCollection();
        $message = $this->messageFactory->createMessage(UserConstants::USER_NPC_KAZON, $closestPhalanx->getUser()->getId(), [sprintf(
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

        // Check uplink status after combat
        $uplinkMessages = $this->checkUplinkStatus($phalanxWrapper);

        // Füge die Uplink-Meldungen zur Nachrichtensammlung hinzu
        if (!empty($uplinkMessages)) {
            // Erstelle eine einzige Nachricht mit allen Uplink-Meldungen
            $message = $this->messageFactory->createMessage(
                UserConstants::USER_NPC_KAZON,
                $closestPhalanx->getUser()->getId(),
                $uplinkMessages
            );
            $messages->add($message);
        }

        // Send messages including combat results and uplink status
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

    /**
     * @return array<string>
     */
    private function checkUplinkStatus(\Stu\Module\Station\Lib\StationWrapperInterface $wrapper): array
    {
        $spacecraft = $wrapper->get();
        $messages = [];

        // Check if uplink system exists and is active
        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK)) {
            return $messages;
        }

        $hasForeigners = $this->troopTransferUtility->foreignerCount($spacecraft) > 0;
        $ownCrewCount = $this->getOwnCrewCount($spacecraft);
        $minOwnCrew = 0;

        $buildplan = $spacecraft->getBuildplan();
        if ($buildplan !== null) {
            $minOwnCrew = $buildplan->getCrew();
        }

        // Deactivate uplink if no foreigners left
        if (!$hasForeigners && $spacecraft->getSystemState(SpacecraftSystemTypeEnum::UPLINK)) {
            $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $message = sprintf('Der Uplink der %s wurde deaktiviert, da sich keine fremden Crewmitglieder mehr an Bord befinden', $spacecraft->getName());
            $this->logger->log($message);
            $messages[] = $message;
        }

        // Deactivate uplink if not enough own crew
        if ($hasForeigners && $spacecraft->getSystemState(SpacecraftSystemTypeEnum::UPLINK) && $ownCrewCount < $minOwnCrew) {
            $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $message = sprintf(
                'Der Uplink der %s wurde deaktiviert, da nicht mehr genügend eigene Crewmitglieder an Bord sind (%d/%d)',
                $spacecraft->getName(),
                $ownCrewCount,
                $minOwnCrew
            );
            $this->logger->log($message);
            $messages[] = $message;
        }

        return $messages;
    }

    private function getOwnCrewCount(Spacecraft $spacecraft): int
    {
        $count = 0;
        foreach ($spacecraft->getCrewAssignments() as $spacecraftCrew) {
            if ($spacecraftCrew->getCrew()->getUser()->getId() === $spacecraft->getUser()->getId()) {
                $count++;
            }
        }
        return $count;
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
            UserConstants::USER_NPC_KAZON,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $header,
            true
        );
    }
}
