<?php

namespace Stu\Lib\Pirate\Component;

use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\LocationInterface;

class MoveOnLayer implements MoveOnLayerInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private SafeFlightRouteInterface $safeFlightRoute,
        private PirateFlightInterface $pirateFlight,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function move(
        ShipWrapperInterface $wrapper,
        ?LocationInterface $target
    ): bool {
        if ($target === null) {
            return false;
        }

        $ship = $wrapper->get();

        $this->logger->log(sprintf('    navigateToTarget: %s', $target->getSectorString()));

        while ($ship->getLocation() !== $target) {

            $lastPosition = $ship->getLocation();

            $this->logger->log(sprintf('    currentPosition: %s', $lastPosition->getSectorString()));

            $xDistance = $target->getX() - $lastPosition->getX();
            $yDistance = $target->getY() - $lastPosition->getY();

            $isInXDirection = $this->moveInXDirection($xDistance, $yDistance);

            $flightRoute = $this->safeFlightRoute->getSafeFlightRoute(
                $ship,
                fn(): Coordinate => new Coordinate(
                    $this->getTargetX($isInXDirection, $lastPosition->getX(), $xDistance),
                    $this->getTargetY($isInXDirection, $lastPosition->getY(), $yDistance)
                )
            );
            if ($flightRoute === null) {
                $this->logger->log('    no safe flight route found');
                return false;
            }

            $this->pirateFlight->movePirate($wrapper, $flightRoute);

            $newPosition = $ship->getLocation();

            $this->logger->log(sprintf('    newPosition: %s', $newPosition->getSectorString()));

            if ($newPosition === $lastPosition) {
                $this->logger->log('    can not move');
                return false;
            }
        }

        return true;
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
}
