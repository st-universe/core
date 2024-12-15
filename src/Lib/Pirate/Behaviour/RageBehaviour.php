<?php

namespace Stu\Lib\Pirate\Behaviour;

use Override;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Prestige\Lib\PrestigeCalculationInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class RageBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private FightLibInterface $fightLib,
        private PrestigeCalculationInterface $prestigeCalculation,
        private PirateAttackInterface $pirateAttack,
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

        $targets = $this->shipRepository->getPirateTargets($leadShip);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn(ShipInterface $target): bool =>
            $leadShip->getLocation() ===  $target->getLocation()
                && $this->fightLib->canAttackTarget($leadShip, $target, true, false, false)
                && !$target->getUser()->isProtectedAgainstPirates()
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
            fn(ShipInterface $a, ShipInterface $b): int =>
            $this->fightLib->calculateHealthPercentage($a) -  $this->fightLib->calculateHealthPercentage($b)
        );

        $weakestTarget = current($filteredTargets);

        $this->logger->logf('    attacking weakestTarget with shipId: %d', $weakestTarget->getId());

        $this->pirateAttack->attackShip($fleet, $weakestTarget);

        $pirateReaction->react(
            $fleet->get(),
            PirateReactionTriggerEnum::ON_RAGE,
            $leadShip,
            $reactionMetadata
        );

        return null;
    }
}
