<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShipUp;

use request;
use ShipMover;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class MoveShipUp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE_UP';

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
        request::setVar('posy', $ship->getPosY() - $fields);
        request::setVar('posx', $ship->getPosX());
        $mover = new ShipMover($ship);
        $game->addInformationMerge($mover->getInformations());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
