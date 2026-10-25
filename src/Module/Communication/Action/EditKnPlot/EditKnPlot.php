<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPlot;

use request;
use Stu\Module\Communication\Lib\PlotMemberServiceInterface;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EditKnPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_PLOT';

    public function __construct(
        private EditKnPlotRequestInterface $editKnPlotRequest,
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

        $plot = $this->rpgPlotRepository->find($this->editKnPlotRequest->getPlotId());

        if ($plot === null || $plot->getUserId() !== $context->getUser()->getId()) {
            return;
        }
        $title = $this->editKnPlotRequest->getTitle();
        $description = $this->editKnPlotRequest->getText();

        if (mb_strlen($title) < 6) {
            $context->getInfo()->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }
        if (mb_strlen($title) > 80) {
            $context->getInfo()->addInformation(_('Der Titel ist zu lang (maximal 80 Zeichen)'));
            return;
        }

        if ($plot->getTitle() === $title && $plot->getDescription() === $description) {
            $context->getInfo()->addInformation(_('Der Plot wurde nicht geändert'));
            $context->setView(ShowKnPlot::VIEW_IDENTIFIER);
            return;
        }

        $plot->setTitle($title);
        $plot->setDescription($description);
        $plot->setEditDate($this->stuTime->time());

        $this->rpgPlotRepository->save($plot);

        $this->plotMemberService->notifyMembers(
            $plot,
            sprintf(_('Der Besitzer hat den RPG-Plot "%s" bearbeitet'), $plot->getTitle())
        );

        $context->getInfo()->addInformation(_('Der Plot wurde editiert'));

        $context->setView(ShowKnPlot::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
