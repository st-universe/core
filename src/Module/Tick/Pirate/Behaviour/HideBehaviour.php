<?php

namespace Stu\Module\Tick\Pirate\Behaviour;

use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tick\Pirate\Component\PirateFlightInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

class HideBehaviour implements PirateBehaviourInterface
{
    private StarSystemRepositoryInterface $starSystemRepository;

    private FlightRouteFactoryInterface $flightRouteFactory;

    private PirateFlightInterface $pirateFlight;

    private RandomSystemEntryInterface $randomSystemEntry;

    private StuRandom $stuRandom;

    private LoggerUtilInterface $logger;

    public function __construct(
        StarSystemRepositoryInterface $starSystemRepository,
        FlightRouteFactoryInterface $flightRouteFactory,
        PirateFlightInterface $pirateFlight,
        RandomSystemEntryInterface $randomSystemEntry,
        StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->starSystemRepository = $starSystemRepository;
        $this->flightRouteFactory = $flightRouteFactory;
        $this->pirateFlight = $pirateFlight;
        $this->randomSystemEntry = $randomSystemEntry;
        $this->stuRandom = $stuRandom;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function action(FleetWrapperInterface $fleet): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $hideSystems = $this->starSystemRepository->getPirateHides($leadShip);
        if (empty($hideSystems)) {
            $this->logger->log('    no hide system in reach');
            return;
        }

        shuffle($hideSystems);
        $closestHideSystem = current($hideSystems);

        // move to system
        if (
            $leadShip->getSystem() === null
            && $leadShip->isOverSystem() !== $closestHideSystem
        ) {
            $this->navigateToSystem($leadWrapper, $closestHideSystem);
        }

        // reached system?
        if (
            $leadShip->isOverSystem() !== $closestHideSystem
            && $leadShip->getSystem() !== $closestHideSystem
        ) {
            $this->logger->log('    did not reach system');
            return;
        }

        // enter system ?
        if ($leadShip->isOverSystem() === $closestHideSystem) {
            $this->logger->log('    try to enter system');
            $this->enterSystem($leadWrapper, $closestHideSystem);
        }

        // entered system ?
        $systemMap = $leadShip->getStarsystemMap();
        if ($systemMap === null || $systemMap->getSystem() !== $closestHideSystem) {
            $this->logger->log('    did not enter system');
        }
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

        $this->logger->log(sprintf('    navigateToTarget: %s', $target->getSectorString()));

        while ($ship->getCurrentMapField() !== $target) {

            $lastPosition = $ship->getCurrentMapField();

            $this->logger->log(sprintf('    currentPosition: %s', $lastPosition->getSectorString()));

            $xDistance = $target->getX() - $lastPosition->getX();
            $yDistance = $target->getY() - $lastPosition->getY();

            $isInXDirection = $this->moveInXDirection($xDistance, $yDistance);

            $flightRoute = $this->flightRouteFactory->getRouteForCoordinateDestination(
                $ship,
                $this->getTargetX($isInXDirection, $lastPosition->getX(), $xDistance),
                $this->getTargetY($isInXDirection, $lastPosition->getY(), $yDistance)
            );

            $this->pirateFlight->movePirate($wrapper, $flightRoute);

            $newPosition = $ship->getCurrentMapField();

            $this->logger->log(sprintf('    newPosition: %s', $newPosition->getSectorString()));

            if ($newPosition === $lastPosition) {
                return;
            }
        }
    }

    private function getTargetX(bool $isInXDirection, int $currentX, int $xDistance): int
    {
        if (!$isInXDirection) {
            return $currentX;
        }

        $this->logger->log(sprintf('    getTargetX with isInXDirection: %b, currentX: %d, xDistance: %d', $isInXDirection, $currentX, $xDistance));

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

        $this->logger->log(sprintf('    getTargetY with isInXDirection: %b, currentY: %d, yDistance: %d', $isInXDirection, $currentY, $yDistance));

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

        $this->logger->log(sprintf('    moveInXDirection with xDistance: %d, yDistance: %d', $xDistance, $yDistance));

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
}
