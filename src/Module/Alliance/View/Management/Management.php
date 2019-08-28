<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Management implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $game->setPageTitle(_('Allianz verwalten'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_MANAGEMENT=1',
            _('Verwaltung')
        );
        $game->setTemplateFile('html/alliancemanagement.xhtml');
        $game->setTemplateVar('ALLIANCE', $alliance);
    }
}
