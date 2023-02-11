<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DoManualProcessTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PROCESS_TICK';

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        // @todo refactor
        global $container;

        $handlerList = $container->get('process_tick_handler');

        foreach ($handlerList as $process) {
            $process->work();
        }

        $this->entityManager->flush();

        $game->addInformation("Der Process-Tick wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
