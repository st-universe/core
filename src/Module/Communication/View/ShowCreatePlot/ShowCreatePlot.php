<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowCreatePlot;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowCreatePlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CREATE_PLOT';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/createplot.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Plot erstellen')
        );
        $game->setPageTitle(_('Plot erstellen'));
    }
}
