<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowUserPlotList;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowUserPlotList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MYPLOTS';

    public function __construct(private RpgPlotRepositoryInterface $rpgPlotRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/communication/plot/userPlots.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER), _('Eigene Plots'));
        $game->setPageTitle(_('Eigene Plots'));

        $game->setTemplateVar(
            'PLOT_LIST',
            $this->rpgPlotRepository->getByUser($game->getUser())
        );
    }
}
