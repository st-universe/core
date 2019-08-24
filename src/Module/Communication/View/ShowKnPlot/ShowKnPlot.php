<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use RPGPlot;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowKnPlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOT';

    private $showKnPlotRequest;

    public function __construct(
        ShowKnPlotRequestInterface $showKnPlotRequest
    ) {
        $this->showKnPlotRequest = $showKnPlotRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->showKnPlotRequest->getPlotId());

        $game->setTemplateFile('html/plotdetails.xhtml');
        $game->setPageTitle(sprintf('Plot: %s', $plot->getTitleDecoded()));

        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_PLOTLIST=1', _('Plots'));
        $game->appendNavigationPart(
            sprintf(
                'comm.php?%d=1&plotid=%s',
                static::VIEW_IDENTIFIER,
                $plot->getId()
            ),
            $plot->getTitleDecoded()
        );

        $game->setTemplateVar('PLOT', $plot);
    }
}
