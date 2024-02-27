<?php

namespace Stu\Lib\Pirate;

use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\FleetInterface;

class PirateReaction implements PirateReactionInterface
{
    private const REACTION_PROBABILITIES = [
        PirateReactionTriggerEnum::ON_ATTACK->value => [
            PirateBehaviourEnum::DO_NOTHING->value => 30,
            PirateBehaviourEnum::FLY->value => 50,
            PirateBehaviourEnum::HIDE->value => 20
        ],
        PirateReactionTriggerEnum::ON_SCAN->value => [
            PirateBehaviourEnum::DO_NOTHING->value => 60,
            PirateBehaviourEnum::FLY->value => 5,
            PirateBehaviourEnum::ATTACK_SHIP->value => 35
        ],
    ];

    private LoggerUtilInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $behaviours
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    public function react(FleetInterface $fleet, PirateReactionTriggerEnum $reactionTrigger): void
    {
        // check if fleet already defeated
        if ($fleet->getShips()->isEmpty()) {
            return;
        }

        $behaviourType = $this->getRandomBehaviourType($reactionTrigger);
        if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
            return;
        }

        $this->logger->log(sprintf('pirateFleetId %d reacts %s', $fleet->getId(), $behaviourType->getDescription()));

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($fleet);

        $this->behaviours[$behaviourType->value]->action($fleetWrapper);
    }

    private function getRandomBehaviourType(PirateReactionTriggerEnum $reactionTrigger): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomOfProbabilities(self::REACTION_PROBABILITIES[$reactionTrigger->value]);

        return PirateBehaviourEnum::from($value);
    }
}
