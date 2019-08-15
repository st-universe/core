<?php

declare(strict_types=1);

namespace Stu\Module\Database\View;

use AccessViolation;
use DatabaseCategory;
use DatabaseEntryData;
use MapRegion;
use request;
use Shiprump;
use StarSystem;
use Stu\Control\GameController;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class DatabaseEntry implements ViewControllerInterface
{

    public function handle(GameControllerInterface $game): void
    {
        $entry = new \DatabaseEntry(request::getIntFatal('ent'));
        $category = new DatabaseCategory(request::getIntFatal('cat'));

        if (!currentUser()->checkDatabaseEntry($entry->getId())) {
            throw new AccessViolation();
        }

        $game->appendNavigationPart(
            "database.php?SHOW_CATEGORY=1&cat=" . $category->getId(),
            "Datenbank: " . $category->getDescription()
        );
        $game->appendNavigationPart(
            "database.php?SHOW_ENTRY=1&cat=" . $category->getId() . "&ent=" . $entry->getId(),
            "Eintrag: " . $entry->getDescription()
        );
        $game->setPageTitle("/ Datenbankeintrag: " . $entry->getDescription());
        $game->setTemplateFile('html/databaseentry.xhtml');

        $this->addSpecialVars($game, $entry);
        $game->setTemplateVar('ENTRY', $entry);
    }

    protected function addSpecialVars(GameController $game, DatabaseEntryData $entry)
    {
        switch ($entry->getType()) {
            case DATABASE_TYPE_REGION:
                $game->setTemplateVar('REGION', new MapRegion($entry->getObjectId()));
                break;
            case DATABASE_TYPE_RUMP:
                $game->setTemplateVar('RUMP', new ShipRump($entry->getObjectId()));
                break;
            case DATABASE_TYPE_STARSYSTEM:
                $game->setTemplateVar('SYSTEM', new StarSystem($entry->getObjectId()));
                break;
        }
    }
}