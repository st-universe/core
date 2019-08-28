<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

use RPGPlot;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class EndKnPlot implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_END_PLOT';

    private $endKnPlotRequest;

    public function __construct(
        EndKnPlotRequestInterface $endKnPlotRequest
    ) {
        $this->endKnPlotRequest = $endKnPlotRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->endKnPlotRequest->getPlotId());
        if ($plot->getUserId() != $game->getUser()->getId()) {
            return;
        }
        if (!$plot->isActive()) {
            return;
        }
        $plot->setEndDate(time());
        $plot->save();

        $game->addInformation(_('Der Plot wurde beendet'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
