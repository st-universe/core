<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\Overview;

use Fleet;
use Ship;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $fleets = Fleet::getFleetsByUser($userId);
        $bases = Ship::getObjectsBy("WHERE user_id=" . $userId . " AND fleets_id=0 AND is_base=1 ORDER BY id");
        $ships = Ship::getObjectsBy("WHERE user_id=" . $userId . " AND fleets_id=0 AND is_base=0 ORDER BY id");

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->setPageTitle(_('/ Schiffe'));
        $game->setTemplateFile('html/shiplist.xhtml');

        $game->setTemplateVar('MAX_POINTS_PER_FLEET', POINTS_PER_FLEET);
        $game->setTemplateVar(
            'SHIPS_AVAILABLE',
            $fleets !== [] || $ships !== [] || $bases !== []
        );
        $game->setTemplateVar(
            'FLEETS',
            $fleets
        );
        $game->setTemplateVar(
            'BASES',
            $bases
        );
        $game->setTemplateVar(
            'SHIPS',
            $ships
        );
    }
}
