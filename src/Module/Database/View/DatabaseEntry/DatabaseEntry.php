<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use AccessViolation;
use MapRegion;
use Ship;
use Shiprump;
use StarSystem;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Category;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class DatabaseEntry implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_ENTRY';

    private $databaseEntryRequest;

    private $databaseCategoryRepository;

    private $databaseEntryRepository;

    private $databaseUserRepository;

    public function __construct(
        DatabaseEntryRequestInterface $databaseEntryRequest,
        DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        DatabaseEntryRepositoryInterface $databaseEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository
    )
    {
        $this->databaseEntryRequest = $databaseEntryRequest;
        $this->databaseCategoryRepository = $databaseCategoryRepository;
        $this->databaseEntryRepository = $databaseEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $entry_id = $this->databaseEntryRequest->getEntryId();
        $category_id = $this->databaseEntryRequest->getCategoryId();

        /**
         * @var DatabaseEntryInterface $entry
         */
        $entry = $this->databaseEntryRepository->find($entry_id);
        $category = $this->databaseCategoryRepository->find($category_id);

        if ($this->databaseUserRepository->exists((int) $game->getUser()->getId(), $entry->getId()) === false) {
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

    protected function addSpecialVars(GameControllerInterface $game, DatabaseEntryInterface $entry)
    {
        $entry_object_id = $entry->getObjectId();

        switch ($entry->getTypeObject()->getId()) {
            case DATABASE_TYPE_POI:
                $game->setTemplateVar('POI', new Ship($entry_object_id));
                break;
            case DATABASE_TYPE_MAP:
                $game->setTemplateVar('REGION', new MapRegion($entry_object_id));
                break;
            case DATABASE_TYPE_SHIPRUMP:
                $game->setTemplateVar('RUMP', new Shiprump($entry_object_id));
                break;
            case DATABASE_TYPE_STARSYSTEM:
                $game->setTemplateVar('SYSTEM', new StarSystem($entry_object_id));
                break;
        }
    }
}