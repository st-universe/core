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
        $allianceId = $alliance->getId();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }
        $game->setPageTitle(_('Allianz editieren'));

        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_ALLIANCE=1&id=%s', $allianceId),
            _('Allianz anzeigen')
        );
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_ALLIANCE=1&id=%s', $allianceId),
            _('Allianz editieren')
        );
        $game->setTemplateFile('html/allianceedit.xhtml');
        $game->setTemplateVar('ALLIANCE', $alliance);
    }
}
