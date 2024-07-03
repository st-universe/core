<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Scripts;

use Override;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowScripts implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SCRIPTS';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/scripts.twig');
        $game->appendNavigationPart('/admin/?SHOW_SCRIPTS=1', _('Scripts'));
        $game->setPageTitle(_('Scripts'));

        $game->setTemplateVar('DEFAULT_LAYER', MapEnum::DEFAULT_LAYER);
    }
}
