<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowCreatePlot;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowCreatePlot implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREATE_PLOT';

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setViewTemplate('html/communication/plot/createPlot.twig');
        $game->appendNavigationPart('communication.php', _('KommNet'));
        $game->appendNavigationPart(
            sprintf('communication.php?%s=1', self::VIEW_IDENTIFIER),
            _('Plot erstellen')
        );
        $game->setPageTitle(_('Plot erstellen'));
    }
}
