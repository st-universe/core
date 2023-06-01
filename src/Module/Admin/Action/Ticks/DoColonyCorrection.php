<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DoColonyCorrection implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONY_CORRECTION';

    private ColonyCorrectorInterface $colonyCorrector;

    public function __construct(
        ColonyCorrectorInterface $colonyCorrector
    ) {
        $this->colonyCorrector = $colonyCorrector;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->colonyCorrector->correct();

        $game->addInformation("Korrektur der Kolonien wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
