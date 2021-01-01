<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;

final class DoManualColonyTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONY_TICK';

    private ColonyTickManagerInterface $colonyTickManager;

    public function __construct(
        ColonyTickManagerInterface $colonyTickManager
    ) {
        $this->colonyTickManager = $colonyTickManager;
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

        $this->colonyTickManager->work(1);
        
        $game->addInformation("Der Colony-Tick wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
