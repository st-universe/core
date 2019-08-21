<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Boards;

use AllianceBoard;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Boards implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARDS';

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $game->setPageTitle(_('Allianzforum'));
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_BOARDS=1&id=%d', $alliance->getId()),
            _('Allianzforum')
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
