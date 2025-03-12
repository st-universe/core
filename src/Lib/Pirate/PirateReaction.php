<?php

namespace Stu\Lib\Pirate;

use Override;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class PirateReaction implements PirateReactionInterface
{
    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        private PirateWrathManagerInterface $pirateWrathManager,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $behaviours
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function checkForPirateReaction(
        SpacecraftInterface $target,
        PirateReactionTriggerEnum $reactionTrigger,
        SpacecraftInterface $triggerSpacecraft
    ): bool {

        $targetFleet = $target instanceof ShipInterface ? $target->getFleet() : null;
        if (
            $targetFleet === null
            || $targetFleet->getUser()->getId() !== UserEnum::USER_NPC_KAZON
        ) {
            return false;
        }

        $this->react(
            $targetFleet,
            $reactionTrigger,
            $triggerSpacecraft,
            new PirateReactionMetadata()
        );

        return true;
    }

    #[Override]
    public function react(
        FleetInterface $fleet,
        PirateReactionTriggerEnum $reactionTrigger,
        SpacecraftInterface $triggerSpacecraft,
        PirateReactionMetadata $reactionMetadata
    ): void {
        $this->pirateWrathManager->increaseWrathViaTrigger($triggerSpacecraft->getUser(), $reactionTrigger);

        // check if fleet already defeated
        if ($fleet->getShips()->isEmpty()) {
            $this->logger->logf('pirateFleet %s has no ships left, no reaction triggered', $fleet->getName());
            return;
        }

        $fleetWrapper = $this->spacecraftWrapperFactory->wrapFleet($fleet);

        $behaviourType = $this->getRandomBehaviourType($reactionTrigger);
        if (
            $behaviourType->needsWeapons()
            && !$this->canAnyoneFire($fleetWrapper)
        ) {
            $this->logger->logf('pirateFleet %s cant fire, no reaction triggered', $fleet->getName());
            return;
        }

        $this->logger->log(sprintf(
            'pirateFleetId %d reacts on %s from "%s" (%d) with %s',
            $fleet->getId(),
            $reactionTrigger->name,
            $triggerSpacecraft->getName(),
            $triggerSpacecraft->getId(),
            $behaviourType->name
        ));

        if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
            return;
        }


        $alternativeBehaviour = $this->action($behaviourType, $fleetWrapper, $reactionMetadata, $triggerSpacecraft);
        if (
            $reactionTrigger->triggerAlternativeReaction()
            &&  $alternativeBehaviour !== null
        ) {
            $this->logger->log(sprintf(
                'pirateFleetId %d does alternative behaviour %s',
                $fleet->getId(),
                $alternativeBehaviour->name
            ));
            $this->action($alternativeBehaviour, $fleetWrapper, $reactionMetadata, $triggerSpacecraft);
        }

        if ($reactionTrigger === PirateReactionTriggerEnum::ON_ATTACK) {
            $this->action(PirateBehaviourEnum::GO_ALERT_RED, $fleetWrapper, $reactionMetadata, null);
        }

        $this->action(PirateBehaviourEnum::DEACTIVATE_SHIELDS, $fleetWrapper, $reactionMetadata, null);
    }

    private function canAnyoneFire(FleetWrapperInterface $fleetWrapper): bool
    {
        return $fleetWrapper->getShipWrappers()->exists(fn(int $key, ShipWrapperInterface $wrapper): bool => $wrapper->canFire());
    }

    private function getRandomBehaviourType(PirateReactionTriggerEnum $reactionTrigger): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomKeyOfProbabilities($reactionTrigger->getBehaviourProbabilities());

        return PirateBehaviourEnum::from($value);
    }

    private function action(
        PirateBehaviourEnum $behaviour,
        FleetWrapperInterface $fleetWrapper,
        PirateReactionMetadata $reactionMetadata,
        ?SpacecraftInterface $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $reactionMetadata->addReaction($behaviour);

        $alternativeBehaviour = $this->behaviours[$behaviour->value]->action(
            $fleetWrapper,
            $this,
            $reactionMetadata,
            $triggerSpacecraft
        );

        $this->reloadMinimalEps->reload($fleetWrapper);

        return $alternativeBehaviour;
    }
}
