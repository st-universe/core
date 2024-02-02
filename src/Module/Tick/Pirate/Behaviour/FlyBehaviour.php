<?php

namespace Stu\Module\Tick\Pirate\Behaviour;

use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Tick\Pirate\Component\PirateFlightInterface;

class FlyBehaviour implements PirateBehaviourInterface
{
    private FlightRouteFactoryInterface $flightRouteFactory;

    private PirateFlightInterface $pirateFlight;

    private StuRandom $stuRandom;

    private LoggerUtilInterface $logger;

    public function __construct(
        FlightRouteFactoryInterface $flightRouteFactory,
        PirateFlightInterface $pirateFlight,
        StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->flightRouteFactory = $flightRouteFactory;
        $this->pirateFlight = $pirateFlight;
        $this->stuRandom = $stuRandom;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function action(FleetWrapperInterface $fleet): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $lastPosition = $leadShip->getCurrentMapField();

        $this->logger->log(sprintf('currentPosition: %s', $lastPosition->getSectorString()));

        $isInXDirection = $this->stuRandom->rand(0, 1) === 0;
        $maxFields = $leadShip->getSensorRange() * 2;

        $flightRoute = $this->flightRouteFactory->getRouteForCoordinateDestination(
            $leadShip,
            $isInXDirection ? $lastPosition->getX() + $this->stuRandom->rand(-$maxFields, $maxFields) : $lastPosition->getX(),
            $isInXDirection ? $lastPosition->getY() : $lastPosition->getY() + $this->stuRandom->rand(-$maxFields, $maxFields)
        );

        $this->pirateFlight->movePirate($leadWrapper, $flightRoute);

        $newPosition = $leadShip->getCurrentMapField();

        $this->logger->log(sprintf('newPosition: %s', $newPosition->getSectorString()));
    }
}
