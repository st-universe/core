<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Edit;

use AccessViolation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Edit implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'EDIT_ALLIANCE';

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }
        $game->setPageTitle(_('Allianz editieren'));

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?EDIT_ALLIANCE=1',
            _('Editieren')
        );
        $game->setTemplateFile('html/allianceedit.xhtml');
        $game->setTemplateVar('ALLIANCE', $alliance);
    }
}
