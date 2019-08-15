<?php

declare(strict_types=1);

namespace Stu\Module\Database\View;

use DatabaseCategory;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

class Overview implements ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            "database.php",
            "Datenbank"
        );
        $game->setPageTitle(_('/ Datenbank'));
        $game->setTemplateFile('html/database.xhtml');

        $game->setTemplateVar('RUMP_LIST', DatabaseCategory::getCategoriesByType(DATABASE_TYPE_SHIPRUMP));
        $game->setTemplateVar('RPG_SHIP_LIST', DatabaseCategory::getCategoriesByType(DATABASE_TYPE_RPGSHIPS));
        $game->setTemplateVar('POI_LIST', DatabaseCategory::getCategoriesByType(DATABASE_TYPE_TRADEPOSTS));
        $game->setTemplateVar('MAP_LIST', DatabaseCategory::getCategoriesByType(DATABASE_TYPE_MAP));
    }
}
