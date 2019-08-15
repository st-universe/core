<?php

declare(strict_types=1);

namespace Stu\Module\Database\View;

use DatabaseCategory;
use request;
use Stu\Control\GameController;
use Stu\Control\ViewControllerInterface;

final class DisplayCategory implements ViewControllerInterface
{

    public function handle(GameController $game): void
    {
        $category = new DatabaseCategory(request::getIntFatal('cat'));

        $game->appendNavigationPart(
            "database.php?SHOW_CATEGORY=1&cat=".$category->getId(),
            "Datenbank: " . $category->getDescription()
        );
        $game->setPageTitle("/ Datenbank: ".$category->getDescription());
        $game->setTemplateFile('html/databasecategory.xhtml');
        $game->setTemplateVar('CATEGORY', $category);
    }
}