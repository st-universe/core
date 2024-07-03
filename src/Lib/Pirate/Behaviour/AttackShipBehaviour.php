<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class AttackShipBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private DistanceCalculationInterface $distanceCalculation,
        private PirateNavigationInterface $pirateNavigation,
        private FightLibInterface $fightLib,
        private PirateAttackInterface $pirateAttack,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $piratePrestige = $this->prestigeOfShipOrFleet($leadShip);

        $this->logger->log(sprintf('    piratePrestige %d', $piratePrestige));

        $targets = $this->shipRepository->getPirateTargets($leadShip);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn (ShipInterface $target): bool =>
            $this->fightLib->canAttackTarget($leadShip, $target, true, false, false)
                && ($target === $triggerShip
                    || $this->targetHasEnoughPrestige($piratePrestige, $target))
        );

        $this->logger->log(sprintf('    %d filtered targets in reach', count($filteredTargets)));

        if ($filteredTargets === []) {
            return null;
        }

        usort(
            $filteredTargets,
            fn (ShipInterface $a, ShipInterface $b): int =>
            $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b)
        );

        $closestShip = current($filteredTargets);

        if ($this->pirateNavigation->navigateToTarget($fleet, $closestShip->getCurrentMapField())) {
            $this->pirateAttack->attackShip($fleet, $closestShip);
        }

        return null;
    }

    private function targetHasEnoughPrestige(int $piratePrestige, ShipInterface $target): bool
    {
        $targetPrestige = $this->prestigeOfShipOrFleet($target);
        $this->logger->log(sprintf('      targetPrestige %d', $targetPrestige));

        return $targetPrestige >= 0.33 * $piratePrestige;
    }

    private function prestigeOfShipOrFleet(ShipInterface $ship): int
    {
        $fleet = $ship->getFleet();
        if ($fleet !== null) {
            return array_reduce(
                $fleet->getShips()->toArray(),
                fn (int $value, ShipInterface $fleetShip): int => $value + $fleetShip->getRump()->getPrestige(),
                0
            );
        }

        return $ship->getRump()->getPrestige();
    }
}
