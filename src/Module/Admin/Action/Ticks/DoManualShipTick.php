<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use request;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DoManualShipTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHIP_TICK';

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
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

        $ship = $this->shipRepository->find(request::postInt('shiptickid'));

        $this->shipTick->work($ship);
        
        $game->addInformation("Der Schiff-Tick für dieses Schiff wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
