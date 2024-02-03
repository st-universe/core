<?php

namespace Stu\Module\Tick\Pirate\Behaviour;

use Stu\Lib\InformationWrapper;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCoreInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tick\Pirate\Component\PirateFlightInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class AttackShipBehaviour implements PirateBehaviourInterface
{
    private ShipRepositoryInterface $shipRepository;

    private DistanceCalculationInterface $distanceCalculation;

    private FlightRouteFactoryInterface $flightRouteFactory;

    private PirateFlightInterface $pirateFlight;

    private RandomSystemEntryInterface $randomSystemEntry;

    private FightLibInterface $fightLib;

    private ShipAttackCoreInterface $shipAttackCore;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private StuRandom $stuRandom;

    private LoggerUtilInterface $logger;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        DistanceCalculationInterface $distanceCalculation,
        FlightRouteFactoryInterface $flightRouteFactory,
        PirateFlightInterface $pirateFlight,
        RandomSystemEntryInterface $randomSystemEntry,
        FightLibInterface $fightLib,
        ShipAttackCoreInterface $shipAttackCore,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->distanceCalculation = $distanceCalculation;
        $this->flightRouteFactory = $flightRouteFactory;
        $this->pirateFlight = $pirateFlight;
        $this->randomSystemEntry = $randomSystemEntry;
        $this->fightLib = $fightLib;
        $this->shipAttackCore = $shipAttackCore;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->stuRandom = $stuRandom;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function action(FleetWrapperInterface $fleet): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $targets = $this->shipRepository->getPirateTargets($leadShip);
        if (empty($targets)) {
            $this->logger->log('no ship targets in reach');
            return;
        }

        usort($targets, fn (ShipInterface $a, ShipInterface $b) =>
        $this->distanceCalculation->shipToShipDistance($leadShip, $a) - $this->distanceCalculation->shipToShipDistance($leadShip, $b));

        $closestShip = current($targets);

        $system = $closestShip->getSystem();

        // move to system
        if (
            $system !== null
            && $leadShip->getSystem() === null
            && $leadShip->isOverSystem() !== $system
        ) {
            $this->navigateToSystem($leadWrapper, $system);
        }

        // reached system?
        if (
            $system !== null
            && $leadShip->isOverSystem() !== $system
            && $leadShip->getSystem() !== $system
        ) {
            $this->logger->log('did not reach system');
            return;
        }

        // enter system ?
        if (
            $system !== null
            && $leadShip->isOverSystem() === $system
        ) {
            $this->logger->log('try to enter system');
            $this->enterSystem($leadWrapper, $system);
        }

        // entered system ?
        $systemMap = $leadShip->getStarsystemMap();
        if (
            $system !== null
            && ($systemMap === null || $systemMap->getSystem() !== $system)
        ) {
            $this->logger->log('did not enter system');
            return;
        }

        // move to ship and rub
        if ($this->navigateToShip($leadWrapper, $closestShip)) {
            $this->logger->log('reached closestShip');
        } else {
            $this->logger->log('did not reach ship');
            return;
        }

        $this->attackShip($fleet, $closestShip);
    }

    private function navigateToSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): void
    {
        $this->navigateToTarget($wrapper, $system->getMapField());
    }

    private function navigateToTarget(ShipWrapperInterface $wrapper, MapInterface|StarSystemMapInterface|null $target): void
    {
        if ($target === null) {
            return;
        }

        $ship = $wrapper->get();

        $this->logger->log(sprintf('navigateToTarget: %s', $target->getSectorString()));

        while ($ship->getCurrentMapField() !== $target) {

            $lastPosition = $ship->getCurrentMapField();

            $this->logger->log(sprintf('currentPosition: %s', $lastPosition->getSectorString()));

            $xDistance = $target->getX() - $lastPosition->getX();
            $yDistance = $target->getY() - $lastPosition->getY();

            if ($xDistance === 0 && $yDistance === 0) {
                break;
            }

            $isInXDirection = $this->moveInXDirection($xDistance, $yDistance);

            $flightRoute = $this->flightRouteFactory->getRouteForCoordinateDestination(
                $ship,
                $this->getTargetX($isInXDirection, $lastPosition->getX(), $xDistance),
                $this->getTargetY($isInXDirection, $lastPosition->getY(), $yDistance)
            );

            $this->pirateFlight->movePirate($wrapper, $flightRoute);

            $newPosition = $ship->getCurrentMapField();

            $this->logger->log(sprintf('newPosition: %s', $newPosition->getSectorString()));

            if ($newPosition === $lastPosition) {
                break;
            }
        }
    }

    private function getTargetX(bool $isInXDirection, int $currentX, int $xDistance): int
    {
        if (!$isInXDirection) {
            return $currentX;
        }

        $this->logger->log(sprintf('getTargetX with isInXDirection: %b, currentX: %d, xDistance: %d', $isInXDirection, $currentX, $xDistance));

        return $currentX + $this->stuRandom->rand(
            $xDistance > 0 ? 1 : $xDistance,
            $xDistance > 0 ? $xDistance : -1
        );
    }

    private function getTargetY(bool $isInXDirection, int $currentY, int $yDistance): int
    {
        if ($isInXDirection) {
            return $currentY;
        }

        $this->logger->log(sprintf('getTargetY with isInXDirection: %b, currentY: %d, yDistance: %d', $isInXDirection, $currentY, $yDistance));

        return $currentY + $this->stuRandom->rand(
            $yDistance > 0 ? 1 : $yDistance,
            $yDistance > 0 ? $yDistance : -1
        );
    }

    private function moveInXDirection(int $xDistance, int $yDistance): bool
    {
        if ($yDistance === 0) {
            return true;
        }

        if ($xDistance === 0) {
            return false;
        }

        $this->logger->log(sprintf('moveInXDirection with xDistance: %d, yDistance: %d', $xDistance, $yDistance));

        return $this->stuRandom->rand(1, abs($xDistance) + abs($yDistance)) <= abs($xDistance);
    }

    private function enterSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): void
    {
        $destination = $this->randomSystemEntry->getRandomEntryPoint($wrapper->get(), $system);

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $destination
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);
    }

    private function navigateToShip(ShipWrapperInterface $wrapper, ShipInterface $ship): bool
    {
        $this->navigateToTarget($wrapper, $ship->getCurrentMapField());

        return $wrapper->get()->getCurrentMapField() === $ship->getCurrentMapField();
    }

    private function attackShip(FleetWrapperInterface $fleetWrapper, ShipInterface $target): void
    {
        $leadWrapper = $fleetWrapper->getLeadWrapper();
        $ship = $fleetWrapper->getLeadWrapper()->get();

        if (!$this->fightLib->canAttackTarget($ship, $target, false)) {
            $this->logger->log('can not attack target');
            return;
        }

        $isFleetFight = false;
        $informations = new InformationWrapper();

        $this->shipAttackCore->foo(
            $leadWrapper,
            $this->shipWrapperFactory->wrapShip($target),
            $isFleetFight,
            $informations
        );
    }
}
