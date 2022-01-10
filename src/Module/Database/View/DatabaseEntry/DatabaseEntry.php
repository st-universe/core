<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Stu\Exception\AccessViolation;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Category;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class DatabaseEntry implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_ENTRY';

    private DatabaseEntryRequestInterface $databaseEntryRequest;

    private DatabaseCategoryRepositoryInterface $databaseCategoryRepository;

    private DatabaseEntryRepositoryInterface $databaseEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private MapRegionRepositoryInterface $mapRegionRepository;

    private StarSystemRepositoryInterface $starSystemRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        DatabaseEntryRequestInterface $databaseEntryRequest,
        DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        DatabaseEntryRepositoryInterface $databaseEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        MapRegionRepositoryInterface $mapRegionRepository,
        StarSystemRepositoryInterface $starSystemRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->databaseEntryRequest = $databaseEntryRequest;
        $this->databaseCategoryRepository = $databaseCategoryRepository;
        $this->databaseEntryRepository = $databaseEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->mapRegionRepository = $mapRegionRepository;
        $this->starSystemRepository = $starSystemRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipRepository = $shipRepository;
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

        if ($this->databaseUserRepository->exists((int)$game->getUser()->getId(), $entry->getId()) === false) {
            throw new AccessViolation();
        }

        $entry_name = $entry->getDescription();

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d',
                Category::VIEW_IDENTIFIER,
                $category_id,
            ),
            $category->getDescription()
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
            case DatabaseEntryTypeEnum::DATABASE_TYPE_POI:
                $game->setTemplateVar('POI', $this->shipRepository->find($entry_object_id));
                break;
            case DatabaseEntryTypeEnum::DATABASE_TYPE_MAP:
                $game->setTemplateVar('REGION', $this->mapRegionRepository->find($entry_object_id));
                break;
            case DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP:
                $game->setTemplateVar('RUMP', $this->shipRumpRepository->find($entry_object_id));
                break;
            case DatabaseEntryTypeEnum::DATABASE_TYPE_STARSYSTEM:
                $starSystem = $this->starSystemRepository->find($entry_object_id);
                $fields = [];
                foreach ($starSystem->getFields() as $obj) {
                    $fields['fields'][$obj->getSY()][] = $obj;
                }
                $fields['xaxis'] = range(1, $starSystem->getMaxX());
                $game->setTemplateVar('SYSTEM', $starSystem);
                $game->setTemplateVar('FIELDS', $fields);
                break;
        }
    }
}
