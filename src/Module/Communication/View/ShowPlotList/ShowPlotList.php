<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPlotList;

use RPGPlot;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowPlotList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOTLIST';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/plotlist.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER), _('Plots'));
        $game->setPageTitle(_('Plots'));
        $game->setTemplateVar('PLOT_LIST', RPGPlot::getObjectsBy('ORDER BY start_date DESC'));
    }
}
