<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use request;
use ShipMover;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class MoveShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE';

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

        $mover = new ShipMover($ship);
        $game->addInformationMerge($mover->getInformations());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
