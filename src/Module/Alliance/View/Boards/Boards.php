<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Boards;

use AllianceBoard;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Boards implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARDS';

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $game->setPageTitle(_('Allianzforum'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_BOARDS=1',
            _('Forum')
        );
        $game->setTemplateFile('html/allianceboard.xhtml');

        $game->setTemplateVar(
            'BOARDS',
            AllianceBoard::getListByAlliance($alliance->getId())
        );
        $game->setTemplateVar(
            'EDITABLE',
            $alliance->currentUserMayEdit()
        );
    }
}
