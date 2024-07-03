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
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;

class PirateReaction implements PirateReactionInterface
{
    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private ShipWrapperFactoryInterface $shipWrapperFactory,
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
        ShipInterface $target,
        PirateReactionTriggerEnum $reactionTrigger,
        ShipInterface $triggerShip
    ): bool {

        $targetFleet = $target->getFleet();
        if (
            $targetFleet === null
            || $targetFleet->getUser()->getId() !== UserEnum::USER_NPC_KAZON
        ) {
            return false;
        }

        $this->react(
            $targetFleet,
            $reactionTrigger,
            $triggerShip,
            new PirateReactionMetadata()
        );

        return true;
    }

    #[Override]
    public function react(FleetInterface $fleet, PirateReactionTriggerEnum $reactionTrigger, ShipInterface $triggerShip, PirateReactionMetadata $reactionMetadata): void
    {
        $this->pirateWrathManager->increaseWrathViaTrigger($triggerShip->getUser(), $reactionTrigger);

        // check if fleet already defeated
        if ($fleet->getShips()->isEmpty()) {
            $this->logger->logf('pirateFleet %s has no ships left, no reaction triggered', $fleet->getName());
            return;
        }

        $behaviourType = $this->getRandomBehaviourType($reactionTrigger);
        $this->logger->log(sprintf(
            'pirateFleetId %d reacts on %s from "%s" (%d) with %s',
            $fleet->getId(),
            $reactionTrigger->name,
            $triggerShip->getName(),
            $triggerShip->getId(),
            $behaviourType->name
        ));

        if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
            return;
        }

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($fleet);

        $alternativeBehaviour = $this->action($behaviourType, $fleetWrapper, $reactionMetadata, $triggerShip);
        if (
            $reactionTrigger->triggerAlternativeReaction()
            &&  $alternativeBehaviour !== null
        ) {
            $this->logger->log(sprintf(
                'pirateFleetId %d does alternative behaviour %s',
                $fleet->getId(),
                $alternativeBehaviour->name
            ));
            $this->action($alternativeBehaviour, $fleetWrapper, $reactionMetadata, $triggerShip);
        }

        if ($reactionTrigger === PirateReactionTriggerEnum::ON_ATTACK) {
            $this->action(PirateBehaviourEnum::GO_ALERT_RED, $fleetWrapper, $reactionMetadata, null);
        }

        $this->action(PirateBehaviourEnum::DEACTIVATE_SHIELDS, $fleetWrapper, $reactionMetadata, null);
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
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum {

        $reactionMetadata->addReaction($behaviour);

        $alternativeBehaviour = $this->behaviours[$behaviour->value]->action(
            $fleetWrapper,
            $this,
            $reactionMetadata,
            $triggerShip
        );

        $this->reloadMinimalEps->reload($fleetWrapper);

        return $alternativeBehaviour;
    }
}
