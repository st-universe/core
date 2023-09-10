<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Exception\AccessViolation;
use Stu\Lib\Map\VisualPanel\SystemCellData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Category;
use Stu\Orm\Entity\ColonyScanInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyScanRepositoryInterface;
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

    private ColonyScanRepositoryInterface $colonyScanRepository;

    private DatabaseEntryRequestInterface $databaseEntryRequest;

    private DatabaseCategoryRepositoryInterface $databaseCategoryRepository;

    private DatabaseEntryRepositoryInterface $databaseEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private MapRegionRepositoryInterface $mapRegionRepository;

    private StarSystemRepositoryInterface $starSystemRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    public function __construct(
        ColonyScanRepositoryInterface $colonyScanRepository,
        DatabaseEntryRequestInterface $databaseEntryRequest,
        DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        DatabaseEntryRepositoryInterface $databaseEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        MapRegionRepositoryInterface $mapRegionRepository,
        StarSystemRepositoryInterface $starSystemRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->colonyScanRepository = $colonyScanRepository;
        $this->databaseEntryRequest = $databaseEntryRequest;
        $this->databaseCategoryRepository = $databaseCategoryRepository;
        $this->databaseEntryRepository = $databaseEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->mapRegionRepository = $mapRegionRepository;
        $this->starSystemRepository = $starSystemRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipRepository = $shipRepository;
        $this->shipCrewCalculator = $shipCrewCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $entryId = $this->databaseEntryRequest->getEntryId();
        $categoryId = $this->databaseEntryRequest->getCategoryId();

        if (!$this->databaseUserRepository->exists($userId, $entryId)) {
            throw new AccessViolation(sprintf(_('userId %d tried to open databaseEntryId %d, but has not discovered it yet!'), $userId, $entryId));
        }

        /**
         * @var DatabaseEntryInterface $entry
         */
        $entry = $this->databaseEntryRepository->find($entryId);
        $category = $this->databaseCategoryRepository->find($categoryId);

        $entry_name = $entry->getDescription();

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d',
                Category::VIEW_IDENTIFIER,
                $categoryId,
            ),
            $category->getDescription()
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d&ent=%d',
                static::VIEW_IDENTIFIER,
                $categoryId,
                $entryId,
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

    protected function addSpecialVars(GameControllerInterface $game, DatabaseEntryInterface $entry): void
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
                $rump = $this->shipRumpRepository->find($entry_object_id);
                if ($rump === null) {
                    return;
                }

                $game->setTemplateVar('RUMP', $rump);
                $game->setTemplateVar(
                    'MAX_CREW_COUNT',
                    $this->shipCrewCalculator->getMaxCrewCountByRump($rump)
                );
                break;
            case DatabaseEntryTypeEnum::DATABASE_TYPE_STARSYSTEM:
                $starSystem = $this->starSystemRepository->find($entry_object_id);
                $fields = [];
                $userHasColonyInSystem = $this->hasUserColonyInSystem($game->getUser(), $entry_object_id);
                foreach ($starSystem->getFields() as $obj) {
                    $fields['fields'][$obj->getSY()][] = [
                        'systemCellData' => $this->createSystemCellData($obj),
                        'colony' => $obj->getColony(),
                        'showPm' => $userHasColonyInSystem && $this->showPmHref($obj, $game->getUser())
                    ];
                }
                $fields['xaxis'] = range(1, $starSystem->getMaxX());
                $game->setTemplateVar('SYSTEM', $starSystem);
                $game->setTemplateVar('FIELDS', $fields);
                $game->setTemplateVar('COLONYSCANLIST', $this->getColonyScanList($game->getUser(), $entry_object_id));
                break;
        }
    }

    private function createSystemCellData(StarSystemMapInterface $systemMap): SystemCellData
    {
        return new SystemCellData(
            $systemMap->getSx(),
            $systemMap->getSy(),
            $systemMap->getFieldId(),
            false,
            null,
            null
        );
    }

    private function hasUserColonyInSystem(UserInterface $user, int $systemId): bool
    {
        foreach ($user->getColonies() as $colony) {
            if ($colony->getStarsystemMap()->getSystem()->getId() === $systemId) {
                return true;
            }
        }

        return false;
    }

    private function showPmHref(StarSystemMapInterface $map, UserInterface $user): bool
    {
        return $map->getColony() !== null
            && !$map->getColony()->isFree()
            && $map->getColony()->getUser() !== $user;
    }

    /**
     * @return array<ColonyScanInterface>
     */
    public function getColonyScanList(UserInterface $user, int $systemId): iterable
    {
        $scanlist = [];

        foreach ($this->colonyScanRepository->getEntryByUserAndSystem($user->getId(), $systemId) as $element) {
            $i = $element->getColony()->getId();
            $scanlist[$i] = $element;
        }
        return $scanlist;
    }
}
