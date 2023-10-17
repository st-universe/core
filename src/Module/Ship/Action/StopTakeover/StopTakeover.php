<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopTakeover;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class StopTakeover implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_STOP_TAKEOVER';

    private ShipLoaderInterface $shipLoader;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipTakeoverManagerInterface $shipTakeoverManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipTakeoverManager = $shipTakeoverManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();

        $takeover = $ship->getTakeoverActive();
        if ($takeover === null) {
            return;
        }

        $this->shipTakeoverManager->cancelTakeover($takeover);

        $game->addInformationf(
            'Übernahme der %s wurde abgebrochen',
            $takeover->getTargetShip()->getName()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
