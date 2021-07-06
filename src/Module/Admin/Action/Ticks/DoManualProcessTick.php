<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DoManualProcessTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PROCESS_TICK';

    public function __construct()
    {
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        // @todo refactor
        global $container;

        $handlerList = $container->get('process_tick_handler');

        foreach ($handlerList as $process) {
            $process->work();
        }

        $game->addInformation("Der Process-Tick wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
