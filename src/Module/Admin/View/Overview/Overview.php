<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Overview;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{
    public function __construct()
    {
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart('/admin/', _('Übersicht'));
        $game->setTemplateFile('html/admin/overview.xhtml');
        $game->setPageTitle(_('Admin'));
    }
}
