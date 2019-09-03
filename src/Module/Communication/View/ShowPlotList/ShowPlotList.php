<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPlotList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowPlotList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLOTLIST';

    private $rpgPlotRepository;

    public function __construct(
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/plotlist.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER), _('Plots'));
        $game->setPageTitle(_('Plots'));
        $game->setTemplateVar('PLOT_LIST', $this->rpgPlotRepository->findBy([], ['start_date' => 'desc']));
    }
}
