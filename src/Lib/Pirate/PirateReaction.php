<?php

namespace Stu\Lib\Pirate;

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
    private const REACTION_PROBABILITIES = [
        PirateReactionTriggerEnum::ON_ATTACK->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 30,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 30,
            PirateBehaviourEnum::FLY->value => 20,
            PirateBehaviourEnum::HIDE->value => 20,
            PirateBehaviourEnum::DO_NOTHING->value => 10,
        ],
        PirateReactionTriggerEnum::ON_SCAN->value => [
            PirateBehaviourEnum::DO_NOTHING->value => 60,
            PirateBehaviourEnum::RAGE->value => 60,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20,
            PirateBehaviourEnum::FLY->value => 20,
            PirateBehaviourEnum::HIDE->value => 20,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 5,
        ],
        PirateReactionTriggerEnum::ON_INTERCEPTION->value => [
            PirateBehaviourEnum::RAGE->value => 40,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 15,
            PirateBehaviourEnum::DO_NOTHING->value => 10,
            PirateBehaviourEnum::FLY->value => 10,
        ],
        PirateReactionTriggerEnum::ON_SUPPORT_CALL->value => [
            PirateBehaviourEnum::RAGE->value => 100,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20
        ],
        PirateReactionTriggerEnum::ON_RAGE->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::DO_NOTHING->value => 30,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20
        ],
    ];

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
            PirateReactionTriggerEnum::ON_SCAN,
            $triggerShip
        );

        return true;
    }

    public function react(FleetInterface $fleet, PirateReactionTriggerEnum $reactionTrigger, ShipInterface $triggerShip): void
    {
        // check if fleet already defeated
        if ($fleet->getShips()->isEmpty()) {
            return;
        }

        $this->pirateWrathManager->increaseWrath($triggerShip->getUser(), $reactionTrigger);

        $behaviourType = $this->getRandomBehaviourType($reactionTrigger);
        $this->logger->log(sprintf(
            'pirateFleetId %d reacts on %s with %s',
            $fleet->getId(),
            $reactionTrigger->name,
            $behaviourType->name
        ));

        if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
            return;
        }

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($fleet);

        $alternativeBehaviour = $this->action($behaviourType, $fleetWrapper);
        if ($alternativeBehaviour !== null) {
            $this->logger->log(sprintf(
                'pirateFleetId %d does alternative behaviour %s',
                $fleet->getId(),
                $alternativeBehaviour->name
            ));
            $this->action($alternativeBehaviour, $fleetWrapper);
        }

        if ($reactionTrigger === PirateReactionTriggerEnum::ON_ATTACK) {
            $this->action(PirateBehaviourEnum::GO_ALERT_RED, $fleetWrapper);
        }

        $this->action(PirateBehaviourEnum::DEACTIVATE_SHIELDS, $fleetWrapper);
    }

    private function getRandomBehaviourType(PirateReactionTriggerEnum $reactionTrigger): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomOfProbabilities(self::REACTION_PROBABILITIES[$reactionTrigger->value]);

        return PirateBehaviourEnum::from($value);
    }

    private function action(PirateBehaviourEnum $behaviour, FleetWrapperInterface $fleetWrapper): ?PirateBehaviourEnum
    {
        $alternativeBehaviour = $this->behaviours[$behaviour->value]->action($fleetWrapper, $this);

        $this->reloadMinimalEps->reload($fleetWrapper);

        return $alternativeBehaviour;
    }
}
