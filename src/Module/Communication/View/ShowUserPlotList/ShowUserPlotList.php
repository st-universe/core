<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowUserPlotList;

use RPGPlot;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowUserPlotList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MYPLOTS';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/userplotlist.xhtml');
        $game->appendNavigationPart(sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER), _('Eigene Plots'));
        $game->setPageTitle(_('Eigene Plots'));

        $game->setTemplateVar(
            'PLOT_LIST',
            RPGPlot::getObjectsBy(
                sprintf(
                    'WHERE id IN (SELECT plot_id FROM stu_plots_members WHERE user_id=%s) ORDER BY start_date DESC',
                    $game->getUser()->getId()
                )
            )
        );
    }
}
