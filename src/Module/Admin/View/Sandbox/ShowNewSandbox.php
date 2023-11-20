<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Sandbox;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewSandbox implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_SANDBOX';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Neue Sandbox erstellen'));
        $game->setMacroInAjaxWindow('html/admin/sandbox/newSandbox.twig');

        $game->setTemplateVar('SANDBOX_LIST', $game->getUser()->getColonies()->toArray());
        $game->setTemplateVar('SANDBOX_NAME', sprintf('SANDBOX %s', date('d M Y')));
    }
}
