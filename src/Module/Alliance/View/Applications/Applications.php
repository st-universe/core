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
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_APPLICATIONS=1',
            _('Bewerbungen')
        );
        $game->setTemplateFile('html/allianceapplications.xhtml');
        $game->setTemplateVar('APPLICATIONS', $alliance->getPendingApplications());
    }
}
