<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\Component\PirateProtectionInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ShipRepositoryInterface;

class RageBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private readonly ShipRepositoryInterface $shipRepository,
        private readonly FightLibInterface $fightLib,
        private readonly PrestigeCalculationInterface $prestigeCalculation,
        private readonly PirateAttackInterface $pirateAttack,
        private readonly PirateProtectionInterface $pirateProtection,
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
        $leadShip = $leadWrapper->get();

        $targets = $this->shipRepository->getPirateTargets($leadWrapper);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn (Ship $target): bool =>
            $leadShip->getLocation() ===  $target->getLocation()
                && $this->fightLib->canAttackTarget($leadShip, $target, true, false, false)
                && !$this->pirateProtection->isProtectedAgainstPirates($target->getUser())
                && ($target === $triggerSpacecraft
                    || $this->prestigeCalculation->targetHasPositivePrestige($target))
        );

        $this->logger->log(sprintf('    %d filtered targets in reach', count($filteredTargets)));

        if ($filteredTargets === []) {
            return PirateBehaviourEnum::SEARCH_FRIEND;
        }

        foreach ($filteredTargets as $ship) {
            $this->logger->log(sprintf(
                '      shipId %d with %F health',
                $ship->getId(),
                $this->fightLib->calculateHealthPercentage($ship)
            ));
        }

        usort(
            $filteredTargets,
            fn (Ship $a, Ship $b): int =>
            $this->fightLib->calculateHealthPercentage($a) -  $this->fightLib->calculateHealthPercentage($b)
        );

        $weakestTarget = current($filteredTargets);

        $this->logger->logf('    attacking weakestTarget with shipId: %d', $weakestTarget->getId());

        $this->pirateAttack->attackShip($pirateFleetBattleParty, $weakestTarget);

        if ($pirateFleetBattleParty->isDefeated()) {
            $this->logger->log('    pirate fleet was destroyed during attack, no further reaction');
            return null;
        }

        $pirateReaction->react(
            $pirateFleetBattleParty,
            PirateReactionTriggerEnum::ON_RAGE,
            $leadShip,
            $reactionMetadata
        );

        return null;
    }
}
