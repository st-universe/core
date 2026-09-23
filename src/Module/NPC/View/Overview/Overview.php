<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\Overview;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{
    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->appendNavigationPart('/npc', _('Übersicht'));
        $game->setTemplateFile('html/npc/overview.twig');
        $game->setPageTitle(_('NPC'));
    }
}
