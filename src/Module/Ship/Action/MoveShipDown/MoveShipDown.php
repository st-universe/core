<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShipDown;

use request;
use ShipMover;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class MoveShipDown implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE_DOWN';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $fields = request::postString('navapp');
        if ($fields <= 0 || $fields > 9 || strlen($fields) > 1) {
            $fields = 1;
        }
        request::setVar('posy', $ship->getPosY() + $fields);
        request::setVar('posx', $ship->getPosX());
        $mover = new ShipMover($ship);
        $game->addInformationMerge($mover->getInformations());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
