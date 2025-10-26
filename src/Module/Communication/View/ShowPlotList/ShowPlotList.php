<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPlotList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowPlotList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLOTLIST';

    public function __construct(private RpgPlotRepositoryInterface $rpgPlotRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $active_plots = [];
        $ended_plots = [];

        foreach ($this->rpgPlotRepository->getOrderedList() as $plot) {
            if ($plot->getEndDate() === null) {
                $active_plots[] = $plot;
            } else {
                $ended_plots[] = $plot;
            }
        }

        $game->setViewTemplate('html/communication/plot/plots.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(sprintf('comm.php?%s=1', self::VIEW_IDENTIFIER), _('Plots'));
        $game->setPageTitle(_('Plots'));
        $game->setTemplateVar('PLOTS', $active_plots);
        $game->setTemplateVar('ENDED_PLOTS', $ended_plots);
    }
}
