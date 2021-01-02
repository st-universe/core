<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use request;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Tick\Ship\ShipTickInterface;

final class DoManualShipTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHIP_TICK';

    private ShipTickInterface $shipTick;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipTickInterface $shipTick,
        ShipLoaderInterface $shipLoader,
    ) {
        $this->shipTick = $shipTick;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin())
        {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::postInt('shiptickid'),
            $userId
        );

        $this->shipTick->work($ship);
        
        $game->addInformation("Der Schiff-Tick für dieses Schiff wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
