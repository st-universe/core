<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Applications;

use AccessViolation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Applications implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_APPLICATIONS';

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }
        $game->setPageTitle(_('Allianz anzeigen'));
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_ALLIANCE=1&id=%d', $alliance->getId()
            ),
            _('Allianz anzeigen')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_APPLICATIONS=1&id=%d', $alliance->getId()
            ),
            _('Bewerbungen')
        );
        $game->setTemplateFile('html/allianceapplications.xhtml');
        $game->setTemplateVar('APPLICATIONS', $alliance->getPendingApplications());
    }
}
