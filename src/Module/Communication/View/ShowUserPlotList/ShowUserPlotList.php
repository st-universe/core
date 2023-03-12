<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowUserPlotList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowUserPlotList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MYPLOTS';

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    public function __construct(
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/userplotlist.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER), _('Eigene Plots'));
        $game->setPageTitle(_('Eigene Plots'));

        $game->setTemplateVar(
            'PLOT_LIST',
            $this->rpgPlotRepository->getByUser($game->getUser())
        );
    }
}
