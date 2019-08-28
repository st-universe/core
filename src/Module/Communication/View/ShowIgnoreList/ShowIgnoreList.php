<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowIgnoreList;

use Ignorelist;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowIgnoreList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_IGNORELIST';

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setTemplateFile('html/ignorelist.xhtml');
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Ignoreliste')
        );
        $game->setPageTitle(_('Ignoreliste'));

        $game->setTemplateVar('IGNORE_LIST', Ignorelist::getList($userId));
        $game->setTemplateVar('REMOTE_IGNORES', Ignorelist::getRemoteIgnores($userId));
    }
}
