<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Ticks;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowTicks implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TICKS';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/ticks.twig');
        $game->appendNavigationPart('/admin/?SHOW_TICKS=1', _('Ticks'));
        $game->setPageTitle(_('Ticks'));
    }
}
