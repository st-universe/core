<?php

namespace Stu\Lib\Pirate;

use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

class PirateReaction implements PirateReactionInterface
{
    private PirateLoggerInterface $logger;

    /** @param array<int, PirateBehaviourInterface> $behaviours */
    public function __construct(
        private BattlePartyFactoryInterface $battlePartyFactory,
        private ReloadMinimalEpsInterface $reloadMinimalEps,
        private PirateWrathManagerInterface $pirateWrathManager,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $behaviours
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function checkForPirateReaction(
        Spacecraft $target,
        PirateReactionTriggerEnum $reactionTrigger,
        Spacecraft $triggerSpacecraft
    ): bool {

        $targetFleet = $target instanceof Ship ? $target->getFleet() : null;
        if (
            $targetFleet === null
            || $targetFleet->getUser()->getId() !== UserConstants::USER_NPC_KAZON
        ) {
            return false;
        }

        $this->react(
            $this->battlePartyFactory->createPirateFleetBattleParty($targetFleet),
            $reactionTrigger,
            $triggerSpacecraft,
            new PirateReactionMetadata()
        );

        return true;
    }

    #[\Override]
    public function react(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionTriggerEnum $reactionTrigger,
        Spacecraft $triggerSpacecraft,
        PirateReactionMetadata $reactionMetadata
    ): void {

        // check if fleet already defeated
        if ($this->isDefeated($pirateFleetBattleParty, 40)) {
            $this->logger->logf('pirateFleet %s has no ships left, no reaction triggered', $pirateFleetBattleParty->getFleetName());
            return;
        }

        $this->pirateWrathManager->increaseWrathViaTrigger($triggerSpacecraft->getUser(), $reactionTrigger);

        $behaviourType = $this->getRandomBehaviourType($reactionTrigger);
        if (
            $behaviourType->needsWeapons()
            && !$this->canAnyoneFire($pirateFleetBattleParty)
        ) {
            $this->logger->logf('pirateFleet %s cant fire, no reaction triggered', $pirateFleetBattleParty->getFleetName());
            return;
        }

        $this->logger->log(sprintf(
            'pirateFleetId %d reacts on %s from "%s" (%d) with %s',
            $pirateFleetBattleParty->getFleetId(),
            $reactionTrigger->name,
            $triggerSpacecraft->getName(),
            $triggerSpacecraft->getId(),
            $behaviourType->name
        ));

        if ($behaviourType === PirateBehaviourEnum::DO_NOTHING) {
            return;
        }

        $alternativeBehaviour = $this->action($behaviourType, $pirateFleetBattleParty, $reactionMetadata, $triggerSpacecraft);

        if ($this->isDefeated($pirateFleetBattleParty, 41)) {
            $this->logger->log('pirateFleet was destroyed during action, no further reaction');
            return;
        }

        if (
            $reactionTrigger->triggerAlternativeReaction()
            &&  $alternativeBehaviour !== null
        ) {
            $this->logger->log(sprintf(
                'pirateFleetId %d does alternative behaviour %s',
                $pirateFleetBattleParty->getFleetId(),
                $alternativeBehaviour->name
            ));
            $this->action($alternativeBehaviour, $pirateFleetBattleParty, $reactionMetadata, $triggerSpacecraft);

            if ($this->isDefeated($pirateFleetBattleParty, 42)) {
                $this->logger->log('pirateFleet was destroyed during alternative action, no further reaction');
                return;
            }
        }

        if ($reactionTrigger === PirateReactionTriggerEnum::ON_ATTACK) {
            $this->action(PirateBehaviourEnum::GO_ALERT_RED, $pirateFleetBattleParty, $reactionMetadata, null);
        }

        $this->action(PirateBehaviourEnum::DEACTIVATE_SHIELDS, $pirateFleetBattleParty, $reactionMetadata, null);
    }

    private function isDefeated(PirateFleetBattleParty $pirateFleetBattleParty, ?int $salt = null): bool
    {
        return $pirateFleetBattleParty->isDefeated();
    }

    private function canAnyoneFire(PirateFleetBattleParty $pirateFleetBattleParty): bool
    {
        return !$pirateFleetBattleParty->getActiveMembers(true)->isEmpty();
    }

    private function getRandomBehaviourType(PirateReactionTriggerEnum $reactionTrigger): PirateBehaviourEnum
    {
        $value = $this->stuRandom->randomKeyOfProbabilities($reactionTrigger->getBehaviourProbabilities());

        return PirateBehaviourEnum::from($value);
    }

    private function action(
        PirateBehaviourEnum $behaviour,
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        $reactionMetadata->addReaction($behaviour);

        $alternativeBehaviour = $this->behaviours[$behaviour->value]->action(
            $pirateFleetBattleParty,
            $this,
            $reactionMetadata,
            $triggerSpacecraft
        );

        $this->reloadMinimalEps->reload($pirateFleetBattleParty);

        return $alternativeBehaviour;
    }
}
