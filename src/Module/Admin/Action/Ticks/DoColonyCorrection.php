<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DoColonyCorrection implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COLONY_CORRECTION';

    public function __construct(private ColonyCorrectorInterface $colonyCorrector) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);
        $this->colonyCorrector->correct();

        $game->getInfo()->addInformation("Korrektur der Kolonien wurde durchgeführt!");
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
