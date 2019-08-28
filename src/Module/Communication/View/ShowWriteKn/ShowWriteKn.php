<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWriteKn;

use RPGPlot;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowWriteKn implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'WRITE_KN';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/writekn.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Beitrag schreiben')
        );
        $game->setPageTitle("Beitrag schreiben");

        $game->setTemplateVar(
            'ACTIVE_RPG_PLOTS',
            RPGPlot::getObjectsBy(
                sprintf(
                    "WHERE end_date=0 AND id IN (SELECT plot_id FROM stu_plots_members WHERE user_id=%d) ORDER BY start_date DESC",
                    $game->getUser()->getId()
                )
            )
        );
    }
}
