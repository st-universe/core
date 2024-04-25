<?php

namespace Stu\Lib\Pirate;

use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\FleetInterface;

class PirateReaction implements PirateReactionInterface
{
    private const REACTION_PROBABILITIES = [
        PirateReactionTriggerEnum::ON_ATTACK->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 30,
            PirateBehaviourEnum::FLY->value => 20,
            PirateBehaviourEnum::HIDE->value => 20,
            PirateBehaviourEnum::DO_NOTHING->value => 10,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 10
        ],
        PirateReactionTriggerEnum::ON_SCAN->value => [
            PirateBehaviourEnum::DO_NOTHING->value => 60,
            PirateBehaviourEnum::RAGE->value => 60,
            PirateBehaviourEnum::FLY->value => 20,
            PirateBehaviourEnum::HIDE->value => 20,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 10,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 5,
        ],
        PirateReactionTriggerEnum::ON_INTERCEPTION->value => [
            PirateBehaviourEnum::RAGE->value => 60,
            PirateBehaviourEnum::DO_NOTHING->value => 30,
            PirateBehaviourEnum::FLY->value => 15,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 5,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 5,
        ],
        PirateReactionTriggerEnum::ON_SUPPORT_CALL->value => [
            PirateBehaviourEnum::RAGE->value => 100,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20
        ],
        PirateReactionTriggerEnum::ON_RAGE->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::DO_NOTHING->value => 40,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 10
        ],
    ];

    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $behaviours
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
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

        $this->logger->log(sprintf(
            'pirateFleetId %d reacts on %s with %s',
            $fleet->getId(),
            $reactionTrigger->name,
            $behaviourType->getDescription()
        ));

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($fleet);

        $this->behaviours[$behaviourType->value]->action($fleetWrapper, $this);

        if ($reactionTrigger === PirateReactionTriggerEnum::ON_ATTACK) {
            $this->behaviours[PirateBehaviourEnum::GO_ALERT_RED->value]->action($fleetWrapper, $this);
        }
    }

    private function getRandomBehaviourType(PirateReactionTriggerEnum $reactionTrigger): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomOfProbabilities(self::REACTION_PROBABILITIES[$reactionTrigger->value]);

        return PirateBehaviourEnum::from($value);
    }
}
