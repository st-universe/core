<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;

final class DoManualShipTick implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SHIP_TICK';

    public function __construct(private ShipTickManagerInterface $shipTickManager, private ShipTickInterface $shipTick, private ShipLoaderInterface $shipLoader, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        //check if single or all ships
        if (!request::getVarByMethod(request::postvars(), 'shiptickid')) {
            $this->shipTickManager->work();
            $game->addInformation("Der Schiff-Tick für alle Schiffe wurde durchgeführt!");
        } else {
            $shipId = request::postInt('shiptickid');
            $wrapper = $this->shipLoader->find($shipId);

            if ($wrapper === null) {
                throw new ShipDoesNotExistException(_('Ship does not exist!'));
            }

            $this->shipTick->workShip($wrapper);
            $this->entityManager->flush();

            $game->addInformation("Der Schiff-Tick für dieses Schiff wurde durchgeführt!");
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
