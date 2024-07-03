<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Process\ProcessTickHandlerInterface;

final class DoManualProcessTick implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PROCESS_TICK';

    /**
     * @param list<ProcessTickHandlerInterface> $tickHandler
     */
    public function __construct(private EntityManagerInterface $entityManager, private array $tickHandler)
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

        foreach ($this->tickHandler as $process) {
            $process->work();
        }

        $this->entityManager->flush();

        $game->addInformation("Der Process-Tick wurde durchgeführt!");
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
