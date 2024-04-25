<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCoreInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
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
        private ShipAttackCoreInterface $shipAttackCore,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): ?PirateBehaviourEnum
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $piratePrestige = $this->prestigeOfShipOrFleet($leadShip);

        $this->logger->log(sprintf('    piratePrestige %d', $piratePrestige));

        $targets = $this->shipRepository->getPirateTargets($leadShip);

        $this->logger->log(sprintf('    %d targets in reach', count($targets)));

        $filteredTargets = array_filter(
            $targets,
            fn (ShipInterface $target) => $this->targetHasEnoughPrestige($piratePrestige, $target)
                && $this->fightLib->canAttackTarget($leadShip, $target, false)
        );

        $this->logger->log(sprintf('    %d filtered targets in reach', count($filteredTargets)));

        if (empty($filteredTargets)) {
            return null;
        }

        usort(
            $filteredTargets,
            fn (ShipInterface $a, ShipInterface $b) =>
            $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b)
        );

        $closestShip = current($filteredTargets);

        if ($this->pirateNavigation->navigateToTarget($fleet, $closestShip->getCurrentMapField())) {
            $this->attackShip($fleet, $closestShip);
        }

        return null;
    }

    private function targetHasEnoughPrestige(int $piratePrestige, ShipInterface $target): bool
    {
        $targetPrestige = $this->prestigeOfShipOrFleet($target);
        $this->logger->log(sprintf('      targetPrestige %d', $targetPrestige));

        return $targetPrestige >= 0.5 * $piratePrestige;
    }

    private function prestigeOfShipOrFleet(ShipInterface $ship): int
    {
        $fleet = $ship->getFleet();
        if ($fleet !== null) {
            return array_reduce(
                $fleet->getShips()->toArray(),
                fn (int $value, ShipInterface $fleetShip) => $value + $fleetShip->getRump()->getPrestige(),
                0
            );
        }

        return $ship->getRump()->getPrestige();
    }

    private function attackShip(FleetWrapperInterface $fleetWrapper, ShipInterface $target): void
    {
        $leadWrapper = $fleetWrapper->getLeadWrapper();
        $isFleetFight = false;
        $informations = new InformationWrapper();

        $this->shipAttackCore->attack(
            $leadWrapper,
            $this->shipWrapperFactory->wrapShip($target),
            $isFleetFight,
            $informations
        );
    }
}
