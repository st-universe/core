<?php

namespace Stu\Lib\Pirate;

use JBBCode\Parser;
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
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 25,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 30,
            PirateBehaviourEnum::FLY->value => 20,
            PirateBehaviourEnum::HIDE->value => 20,
            PirateBehaviourEnum::DO_NOTHING->value => 10,
        ],
        PirateReactionTriggerEnum::ON_SCAN->value => [
            PirateBehaviourEnum::DO_NOTHING->value => 60,
            PirateBehaviourEnum::RAGE->value => 60,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 15,
            PirateBehaviourEnum::FLY->value => 20,
            PirateBehaviourEnum::HIDE->value => 20,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 5,
        ],
        PirateReactionTriggerEnum::ON_INTERCEPTION->value => [
            PirateBehaviourEnum::RAGE->value => 40,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 15,
            PirateBehaviourEnum::SEARCH_FRIEND->value => 15,
            PirateBehaviourEnum::DO_NOTHING->value => 10,
            PirateBehaviourEnum::FLY->value => 10,
        ],
        PirateReactionTriggerEnum::ON_SUPPORT_CALL->value => [
            PirateBehaviourEnum::RAGE->value => 100,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 10
        ],
        PirateReactionTriggerEnum::ON_RAGE->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 20,
            PirateBehaviourEnum::DO_NOTHING->value => 20
        ],
        PirateReactionTriggerEnum::ON_TRACTOR->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 40,
            PirateBehaviourEnum::DO_NOTHING->value => 20
        ],
        PirateReactionTriggerEnum::ON_BEAM->value => [
            PirateBehaviourEnum::RAGE->value => 50,
            PirateBehaviourEnum::CALL_FOR_SUPPORT->value => 40,
            PirateBehaviourEnum::DO_NOTHING->value => 10
        ],
    ];

    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        private PirateWrathManagerInterface $pirateWrathManager,
        private StuRandom $stuRandom,
        private Parser $bbCodeParser,
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
            $reactionTrigger,
            $triggerShip
        );

        return true;
    }

    public function react(FleetInterface $fleet, PirateReactionTriggerEnum $reactionTrigger, ShipInterface $triggerShip): void
    {
        $this->pirateWrathManager->increaseWrath($triggerShip->getUser(), $reactionTrigger);

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
            $this->bbCodeParser->parse($triggerShip->getName())->getAsText(),
            $triggerShip->getId(),
            $behaviourType->name
        ));

        if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
            return;
        }

        $fleetWrapper = $this->shipWrapperFactory->wrapFleet($fleet);

        $alternativeBehaviour = $this->action($behaviourType, $fleetWrapper, $triggerShip);
        if (
            $reactionTrigger->triggerAlternativeReaction()
            &&  $alternativeBehaviour !== null
        ) {
            $this->logger->log(sprintf(
                'pirateFleetId %d does alternative behaviour %s',
                $fleet->getId(),
                $alternativeBehaviour->name
            ));
            $this->action($alternativeBehaviour, $fleetWrapper, $triggerShip);
        }

        if ($reactionTrigger === PirateReactionTriggerEnum::ON_ATTACK) {
            $this->action(PirateBehaviourEnum::GO_ALERT_RED, $fleetWrapper, null);
        }

        $this->action(PirateBehaviourEnum::DEACTIVATE_SHIELDS, $fleetWrapper, null);
    }

    private function getRandomBehaviourType(PirateReactionTriggerEnum $reactionTrigger): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomOfProbabilities(self::REACTION_PROBABILITIES[$reactionTrigger->value]);

        return PirateBehaviourEnum::from($value);
    }

    private function action(PirateBehaviourEnum $behaviour, FleetWrapperInterface $fleetWrapper, ?ShipInterface $triggerShip): ?PirateBehaviourEnum
    {
        $alternativeBehaviour = $this->behaviours[$behaviour->value]->action(
            $fleetWrapper,
            $this,
            $triggerShip
        );

        $this->reloadMinimalEps->reload($fleetWrapper);

        return $alternativeBehaviour;
    }
}
