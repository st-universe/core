<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maintenance\OrionSlaveTrade;

final class DoOrionSlaveTrade implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ORION_SLAVE_TRADE';

    public function __construct(private OrionSlaveTrade $orionSlaveTrade, private EntityManagerInterface $entityManager) {}

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]');
            return;
        }

        $this->orionSlaveTrade->handle();
        $this->entityManager->flush();
        $game->getInfo()->addInformation('Der Orion Sklavenhandel wurde durchgeführt');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
