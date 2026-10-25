<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ReopenKnPlot;

use request;
use Stu\Module\Communication\Lib\PlotMemberServiceInterface;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ReopenKnPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REOPEN_PLOT';

    public function __construct(
        private ReopenKnPlotRequestInterface $reopenKnPlotRequest,
        private RpgPlotRepositoryInterface $rpgPlotRepository,
        private PlotMemberServiceInterface $plotMemberService,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!request::isPost()) {
            return;
        }

        $plot = $this->rpgPlotRepository->find($this->reopenKnPlotRequest->getPlotId());
        if ($plot === null || $plot->getUserId() !== $context->getUser()->getId() || $plot->isActive()) {
            return;
        }

        $plot->setEndDate(null);
        $plot->setEditDate($this->stuTime->time());
        $this->rpgPlotRepository->save($plot);

        $this->plotMemberService->notifyMembers(
            $plot,
            sprintf(_('Der Besitzer hat den RPG-Plot "%s" erneut eröffnet'), $plot->getTitle())
        );

        $context->getInfo()->addInformation(_('Der Plot wurde erneut eröffnet'));
        $context->setView(ShowKnPlot::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
