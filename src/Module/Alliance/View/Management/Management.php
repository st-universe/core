<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Management;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Management implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MANAGEMENT';

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $game->setPageTitle(_('Allianz anzeigen'));
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_MANAGEMENT=1&id=%s',
                $alliance->getId()),
            _('Verwaltung')
        );
        $game->setTemplateFile('html/alliancemanagement.xhtml');
        $game->setTemplateVar('ALLIANCE', $alliance);
    }
}
