<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPlot;

use RPGPlot;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowKnPlot\ShowKnPlot;

final class EditKnPlot implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_PLOT';

    private $editKnPlotRequest;

    public function __construct(
        EditKnPlotRequestInterface $editKnPlotRequest
    ) {
        $this->editKnPlotRequest = $editKnPlotRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->editKnPlotRequest->getPlotId());

        if ($plot->getUserId() != $game->getUser()->getId()) {
            return;
        }
        $title = $this->editKnPlotRequest->getTitle();
        $description = $this->editKnPlotRequest->getText();

        $plot->setTitle($title);
        $plot->setDescription($description);
        if (mb_strlen($title) < 6) {
            $game->addInformation(_('Der Titel ist zu kurz (mindestens 6 Zeichen)'));
            return;
        }
        $plot->save();

        $game->addInformation(_('Der Plot wurde editiert'));

        $game->setView(ShowKnPlot::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
