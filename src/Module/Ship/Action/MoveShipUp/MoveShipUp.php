<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShipUp;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class MoveShipUp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE_UP';

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

        $fields = request::postString('navapp');
        if ($fields <= 0 || $fields > 9 || strlen($fields) > 1) {
            $fields = 1;
        }

        $this->shipMover->checkAndMove(
            $wrapper,
            $ship->getPosX(),
            $ship->getPosY() - $fields
        );
        $game->addInformationMerge($this->shipMover->getInformations());

        if ($ship->getIsDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
