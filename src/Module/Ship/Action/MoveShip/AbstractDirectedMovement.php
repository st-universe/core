<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

abstract class AbstractDirectedMovement implements ActionControllerInterface
{
    protected MoveShipRequestInterface $moveShipRequest;

    protected FlightRouteFactoryInterface $flightRouteFactory;

    protected StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipLoaderInterface $shipLoader;

    private ShipMoverInterface $shipMover;

    public function __construct(
        MoveShipRequestInterface $moveShipRequest,
        ShipLoaderInterface $shipLoader,
        ShipMoverInterface $shipMover,
        FlightRouteFactoryInterface $flightRouteFactory,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->moveShipRequest = $moveShipRequest;
        $this->shipLoader = $shipLoader;
        $this->shipMover = $shipMover;
        $this->flightRouteFactory = $flightRouteFactory;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

    abstract protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool;

    abstract protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface;

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

        $messages = $this->shipMover->checkAndMove(
            $wrapper,
            $this->getFlightRoute($wrapper)
        );
        $game->addInformationWrapper($messages->getInformationDump());

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }

    private function isSanityCheckFaulty(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew($game)) {
            return true;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return true;
        }

        if ($ship->getHoldingWeb() !== null && $ship->getHoldingWeb()->isFinished()) {
            $game->addInformation(_('Das Schiff ist in einem Energienetz gefangen'));
            return true;
        }

        return $this->isSanityCheckFaultyConcrete($wrapper, $game);
    }
}
