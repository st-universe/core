<?php

declare(strict_types=1);

namespace Stu\Module\Notes\View\Overview;

use Notes;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{

    public function __construct()
    {
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'notes.php',
            _('Notizen')
        );
        $game->setPageTitle(_('/ Notizen'));
        $game->setTemplateFile('html/notes.xhtml');
        $game->setTemplateVar(
            'NOTE_LIST',
            Notes::getListByUser($game->getUser()->getId())
        );
    }
}
