<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceList;

use Alliance;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class AllianceList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LIST';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Allianzliste'));

        if ($game->getUser()->getAllianceId() > 0) {
            $game->appendNavigationPart(
                'alliance.php',
                _('Allianz')
            );
        }

        $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
        $game->setTemplateFile('html/alliancelist.xhtml');

        $game->setTemplateVar(
            'ALLIANCE_LIST',
            Alliance::getList()
        );
    }
}
