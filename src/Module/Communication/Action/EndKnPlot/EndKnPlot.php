<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EndKnPlot implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_END_PLOT';

    private EndKnPlotRequestInterface $endKnPlotRequest;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    public function __construct(
        EndKnPlotRequestInterface $endKnPlotRequest,
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->endKnPlotRequest = $endKnPlotRequest;
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = $this->rpgPlotRepository->find($this->endKnPlotRequest->getPlotId());
        if ($plot === null || $plot->getUserId() !== $game->getUser()->getId()) {
            return;
        }
        if (!$plot->isActive()) {
            return;
        }
        $plot->setEndDate(time());

        $this->rpgPlotRepository->save($plot);

        $game->addInformation(_('Der Plot wurde beendet'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
