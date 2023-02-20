<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShipRight;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class MoveShipRight implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE_RIGHT';

    private ShipLoaderInterface $shipLoader;

    private ShipMoverInterface $shipMover;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipMoverInterface $shipMover
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipMover = $shipMover;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $fields = request::postInt('navapp');
        if ($fields <= 0 || $fields > 9) {
            $fields = 1;
        }
        $this->shipMover->checkAndMove(
            $wrapper,
            $ship->getPosX() + $fields,
            $ship->getPosY()
        );
        $game->addInformationMerge($this->shipMover->getInformations());

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
