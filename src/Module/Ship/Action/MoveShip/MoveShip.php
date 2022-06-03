<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipMover2Interface;
use Stu\Module\Ship\Lib\ShipMoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class MoveShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE';

    private ShipLoaderInterface $shipLoader;

    private ShipMoverInterface $shipMover;

    private ShipMover2Interface $shipMover2;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipMoverInterface $shipMover,
        ShipMover2Interface $shipMover2
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipMover = $shipMover;
        $this->shipMover2 = $shipMover2;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getId() === 7396) {
            $this->shipMover2->checkAndMove(
                $ship,
                request::getIntFatal('posx'),
                request::getIntFatal('posy')
            );
            $game->addInformationMerge($this->shipMover2->getInformations());
        } else {
            $this->shipMover->checkAndMove(
                $ship,
                request::getIntFatal('posx'),
                request::getIntFatal('posy')
            );
            $game->addInformationMerge($this->shipMover->getInformations());
        }

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
