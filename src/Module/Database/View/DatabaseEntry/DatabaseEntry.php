<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Override;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Exception\AccessViolation;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Lib\Map\VisualPanel\Layer\Render\SystemLayerRenderer;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Category;
use Stu\Orm\Entity\ColonyScanInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

final class DatabaseEntry implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ENTRY';

    public function __construct(
        private DatabaseEntryRequestInterface $databaseEntryRequest,
        private DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private MapRegionRepositoryInterface $mapRegionRepository,
        private StarSystemRepositoryInterface $starSystemRepository,
        private ShipRumpRepositoryInterface $shipRumpRepository,
        private ShipCrewCalculatorInterface $shipCrewCalculator,
        private ShipRepositoryInterface $shipRepository,
        private PlanetGeneratorInterface $planetGenerator
    ) {
    }

    #[Override]
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
            case DatabaseEntryTypeEnum::DATABASE_TYPE_PLANET:
                $game->setTemplateVar('FIELD_ARRAY', $this->getPlanetFieldArray($entry_object_id));
                $game->setTemplateVar('SURFACE_TILE_STYLE', $this->getSurfaceTileStyle($entry_object_id));
                break;
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
                $data = [];
                $userHasColonyInSystem = $this->hasUserColonyInSystem($game->getUser(), $entry_object_id);

                $renderer = new SystemLayerRenderer();
                $panel = new class() implements PanelAttributesInterface
                {
                    public function getHeightAndWidth(): string
                    {
                        return 'height: 30px; width: 30px;';
                    }

                    public function getFontSize(): string
                    {
                        return '';
                    }
                };

                foreach ($starSystem->getFields() as $obj) {
                    $data['fields'][$obj->getSY()][] = [

                        'rendered' => $renderer->render($this->createMapData($obj), $panel),
                        'colony' => $obj->getColony(),
                        'showPm' => $userHasColonyInSystem && $this->showPmHref($obj, $game->getUser())
                    ];
                }
                $data['xaxis'] = range(1, $starSystem->getMaxX());
                $game->setTemplateVar('SYSTEM', $starSystem);
                $game->setTemplateVar('DATA', $data);
                $game->setTemplateVar('COLONYSCANLIST', $this->getColonyScanList($game->getUser(), $entry_object_id));
                break;
        }
    }

    /** @return array<int> */
    private function getPlanetFieldArray(int $colonyClassId): array
    {
        $planetConfig = $this->planetGenerator->generateColony(
            $colonyClassId,
            random_int(0, 3)
        );

        return $planetConfig->getFieldArray();
    }

    public function getSurfaceTileStyle(int $colonyClassId): string
    {
        $width = $this->planetGenerator->loadColonyClassConfig($colonyClassId)['sizew'];
        $gridArray = [];
        for ($i = 0; $i < $width; $i++) {
            $gridArray[] = '43px';
        }

        return sprintf('display: grid; grid-template-columns: %s;', implode(' ', $gridArray));
    }

    private function createMapData(StarSystemMapInterface $systemMap): MapData
    {
        return new MapData(
            $systemMap->getSx(),
            $systemMap->getSy(),
            $systemMap->getFieldId(),
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
     * @return array<int, ColonyScanInterface>
     */
    public function getColonyScanList(UserInterface $user, int $systemId): array
    {
        $alliance = $user->getAlliance();

        if ($alliance !== null) {
            $unfilteredScans = array_merge(...$alliance->getMembers()->map(fn (UserInterface $user) => $user->getColonyScans()->toArray()));
        } else {
            $unfilteredScans = $user->getColonyScans()->toArray();
        }


        return $this->filterBySystem($unfilteredScans, $systemId);
    }

    /**
     * @param array<int, ColonyScanInterface> $colonyScans
     * 
     * @return array<int, ColonyScanInterface>
     */
    private function filterBySystem(array $colonyScans, int $systemId): array
    {
        return array_filter(
            $colonyScans,
            fn (ColonyScanInterface $scan): bool => $scan->getColony()->getSystemsId() === $systemId
        );
    }
}
