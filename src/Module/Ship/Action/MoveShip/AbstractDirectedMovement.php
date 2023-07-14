<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

abstract class AbstractDirectedMovement implements ActionControllerInterface
{
    protected MoveShipRequestInterface $moveShipRequest;

    protected  FlightRouteFactoryInterface $flightRouteFactory;

    private ShipLoaderInterface $shipLoader;

    private ShipMoverInterface $shipMover;

    public function __construct(
        MoveShipRequestInterface $moveShipRequest,
        ShipLoaderInterface $shipLoader,
        ShipMoverInterface $shipMover,
        FlightRouteFactoryInterface $flightRouteFactory
    ) {
        $this->moveShipRequest = $moveShipRequest;
        $this->shipLoader = $shipLoader;
        $this->shipMover = $shipMover;
        $this->flightRouteFactory = $flightRouteFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $this->moveShipRequest->getShipId(),
            $userId
        );

        if ($this->isSanityCheckFaulty($wrapper, $game)) {
            return;
        }

        $ship = $wrapper->get();

        $informationWrapper = $this->shipMover->checkAndMove(
            $wrapper,
            $this->getFlightRoute($wrapper)
        );
        $game->addInformationMerge($informationWrapper->getInformations());

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    abstract protected function isSanityCheckFaulty(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool;

    abstract protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface;

    public function performSessionCheck(): bool
    {
        return true;
    }
}
