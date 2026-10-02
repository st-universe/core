<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPlot;

use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class EditKnPlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_PLOT';

    public function __construct(private EditKnPlotRequestInterface $editKnPlotRequest, private RpgPlotRepositoryInterface $rpgPlotRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $plot = $this->rpgPlotRepository->find($this->editKnPlotRequest->getPlotId());

        if ($plot === null || $plot->getUserId() !== $context->getUser()->getId()) {
            return;
        }
        $title = $this->editKnPlotRequest->getTitle();
        $description = $this->editKnPlotRequest->getText();

        $plot->setTitle($title);
        $plot->setDescription($description);
        if (mb_strlen($title) < 6) {
            $context->getInfo()->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }

        $this->rpgPlotRepository->save($plot);

        $context->getInfo()->addInformation(_('Der Plot wurde editiert'));

        $context->setView(ShowKnPlot::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
