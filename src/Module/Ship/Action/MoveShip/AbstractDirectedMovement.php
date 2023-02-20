<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

abstract class AbstractDirectedMovement implements ActionControllerInterface
{
    protected MoveShipRequestInterface $moveShipRequest;

    private ShipLoaderInterface $shipLoader;

    private ShipMoverInterface $shipMover;

    public function __construct(
        MoveShipRequestInterface $moveShipRequest,
        ShipLoaderInterface $shipLoader,
        ShipMoverInterface $shipMover
    ) {
        $this->moveShipRequest = $moveShipRequest;
        $this->shipLoader = $shipLoader;
        $this->shipMover = $shipMover;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $this->moveShipRequest->getShipId(),
            $userId
        );
        $ship = $wrapper->get();

        $this->shipMover->checkAndMove(
            $wrapper,
            $this->getPosX($ship),
            $this->getPosY($ship)
        );
        $game->addInformationMerge($this->shipMover->getInformations());

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    abstract protected function getPosX(ShipInterface $ship): int;

    abstract protected function getPosY(ShipInterface $ship): int;

    public function performSessionCheck(): bool
    {
        return true;
    }
}
