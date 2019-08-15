<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use AccessViolation;
use DatabaseCategory;
use DatabaseEntryData;
use MapRegion;
use Shiprump;
use StarSystem;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Category;

final class DatabaseEntry implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_ENTRY';

    private $databaseEntryRequest;

    public function __construct(
        DatabaseEntryRequestInterface $databaseEntryRequest
    )
    {
        $this->databaseEntryRequest = $databaseEntryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $entry_id = $this->databaseEntryRequest->getEntryId();
        $category_id = $this->databaseEntryRequest->getCategoryId();

        $entry = new \DatabaseEntry($entry_id);
        $category = new DatabaseCategory($category_id);

        if (!currentUser()->checkDatabaseEntry($entry->getId())) {
            throw new AccessViolation();
        }

        $entry_name = $entry->getDescription();

        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d',
                Category::VIEW_IDENTIFIER,
                $category_id,
            ),
            sprintf(
                'Datenbank: %s',
                $category->getDescription(),
            )
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d&ent=%d',
                static::VIEW_IDENTIFIER,
                $category_id,
                $entry_id,
            ),
            sprintf(
                _('Eintrag: %s'),
                $entry_name
            )
        );
        $game->setPageTitle(sprintf(_('/ Datenbankeintrag: %s'), $entry_name));
        $game->setTemplateFile('html/databaseentry.xhtml');

        $this->addSpecialVars($game, $entry);
        $game->setTemplateVar('ENTRY', $entry);
    }

    protected function addSpecialVars(GameControllerInterface $game, DatabaseEntryData $entry)
    {
        $entry_object_id = $entry->getObjectId();

        switch ($entry->getType()) {
            case DATABASE_TYPE_REGION:
                $game->setTemplateVar('REGION', new MapRegion($entry_object_id));
                break;
            case DATABASE_TYPE_RUMP:
                $game->setTemplateVar('RUMP', new ShipRump($entry_object_id));
                break;
            case DATABASE_TYPE_STARSYSTEM:
                $game->setTemplateVar('SYSTEM', new StarSystem($entry_object_id));
                break;
        }
    }
}