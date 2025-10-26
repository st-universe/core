<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Lib\Map\VisualPanel\Layer\Render\SystemLayerRenderer;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Category;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

final class ShowDatabaseEntry implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ENTRY';

    public function __construct(
        private DatabaseEntryRequestInterface $databaseEntryRequest,
        private DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        private DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private MapRegionRepositoryInterface $mapRegionRepository,
        private StarSystemRepositoryInterface $starSystemRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private ShipRepositoryInterface $shipRepository,
        private PlanetGeneratorInterface $planetGenerator,
        private MapRepositoryInterface $mapRepository,
        private EncodedMapInterface $encodedMap
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $entryId = $this->databaseEntryRequest->getEntryId();
        $categoryId = $this->databaseEntryRequest->getCategoryId();

        if (!$this->databaseUserRepository->exists($userId, $entryId)) {
            throw new AccessViolationException(sprintf(
                _('userId %d tried to open databaseEntryId %d, but has not discovered it yet!'),
                $userId,
                $entryId
            ));
        }

        /**
         * @var DatabaseEntry $entry
         */
        $entry = $this->databaseEntryRepository->find($entryId);
        $category = $this->databaseCategoryRepository->find($categoryId);
        if ($category === null) {
            throw new SanityCheckException(sprintf('categoryId %d does not exist', $categoryId));
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
                $categoryId
            ),
            $category->getDescription()
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d&ent=%d',
                self::VIEW_IDENTIFIER,
                $categoryId,
                $entryId
            ),
            sprintf(
                _('Eintrag: %s'),
                $entry_name
            )
        );
        $game->setPageTitle(sprintf(_('/ Datenbankeintrag: %s'), $entry_name));
        $game->setViewTemplate('html/database/databaseEntry.twig');

        $this->addSpecialVars($game, $entry);
        $game->setTemplateVar('ENTRY', $entry);
    }

    private function addSpecialVars(GameControllerInterface $game, DatabaseEntry $entry): void
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
                $region = $this->mapRegionRepository->findOneBy(['database_id' => $entry->getId()]);
                $game->setTemplateVar('REGION', $region);

                if ($region !== null) {
                    $regionId = $region->getId();
                    $mapFields = $this->mapRepository->getMapFieldsByRegion($regionId);

                    if (!empty($mapFields)) {
                        $minX = $maxX = $mapFields[0]->getCx();
                        $minY = $maxY = $mapFields[0]->getCy();
                        $layer = $mapFields[0]->getLayer();
                        $layerId = $layer !== null ? $layer->getId() : null;

                        foreach ($mapFields as $field) {
                            $cx = $field->getCx();
                            $cy = $field->getCy();

                            if ($cx < $minX) {
                                $minX = $cx;
                            }
                            if ($cx > $maxX) {
                                $maxX = $cx;
                            }
                            if ($cy < $minY) {
                                $minY = $cy;
                            }
                            if ($cy > $maxY) {
                                $maxY = $cy;
                            }
                        }

                        $layer = $mapFields[0]->getLayer();
                        $layerWidth = $layer !== null ? $layer->getWidth() : 0;
                        $layerHeight = $layer !== null ? $layer->getHeight() : 0;

                        $minX = max(1, $minX - 1);
                        $minY = max(1, $minY - 1);
                        $maxX = min($layerWidth, $maxX + 1);
                        $maxY = min($layerHeight, $maxY + 1);

                        $allMapFields = [];
                        if ($layerId !== null) {
                            $allMapFields = $this->mapRepository->getByCoordinateRange(
                                $layerId,
                                $minX,
                                $maxX,
                                $minY,
                                $maxY
                            );
                        }

                        $mapData = $this->prepareRegionMapData(
                            $allMapFields,
                            $minX,
                            $maxX,
                            $minY,
                            $maxY
                        );
                        $game->setTemplateVar('MAP_DATA', $mapData);
                    }
                }
                break;
            case DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP:
                $rump = $this->spacecraftRumpRepository->find($entry_object_id);
                if ($rump === null) {
                    return;
                }

                if ($rump->isStation()) {
                    $plan = $this->spacecraftBuildplanRepository->getStationBuildplanByRump($rump->getId());
                    $game->setTemplateVar('PLAN', $plan);
                    if ($plan !== null) {
                        $mods = $plan->getModulesOrdered();
                        $game->setTemplateVar('MODS', $mods);

                        $energymodule = $mods->filter(
                            fn($mod): bool => $mod->getModule()->getType() === SpacecraftModuleTypeEnum::EPS
                        )->first();

                        if ($energymodule !== false) {
                            $energyModule = $energymodule->getModule();
                            $energy = $energyModule->getType()->getModuleRumpWrapperCallable()(
                                $rump,
                                $plan
                            )->getValue($energyModule);

                            $game->setTemplateVar('EPS', $energy);
                        }

                        $sensormodule = $mods->filter(
                            fn($mod): bool => (
                                $mod->getModule()->getType() === SpacecraftModuleTypeEnum::SENSOR
                            )
                        )->first();

                        if ($sensormodule !== false) {
                            $sensorModule = $sensormodule->getModule();
                            $sensor = $sensorModule->getType()->getModuleRumpWrapperCallable()(
                                $rump,
                                $plan
                            )->getValue($sensorModule);

                            $game->setTemplateVar('SENSORRANGE', $sensor);
                        }
                    }
                }

                $game->setTemplateVar('RUMP', $rump);
                $game->setTemplateVar(
                    'MAX_CREW_COUNT',
                    $this->shipCrewCalculator->getMaxCrewCountByRump($rump)
                );
                break;
            case DatabaseEntryTypeEnum::DATABASE_TYPE_STARSYSTEM:
                $starSystem = $this->starSystemRepository->find($entry_object_id);
                if ($starSystem === null) {
                    throw new SanityCheckException(sprintf(
                        'starSystemId %d does not exist',
                        $entry_object_id
                    ));
                }
                $data = [];
                $userHasColonyInSystem = $this->hasUserColonyInSystem($game->getUser(), $entry_object_id);

                $renderer = new SystemLayerRenderer();
                $panel = new class() implements PanelAttributesInterface {
                    #[\Override]
                    public function getHeightAndWidth(): string
                    {
                        return 'height: 30px; width: 30px;';
                    }

                    #[\Override]
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
                $game->setTemplateVar('COLONYSCANLIST', $this->getColonyScanList(
                    $game->getUser(),
                    $entry_object_id
                ));
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

    private function createMapData(StarSystemMap $systemMap): MapData
    {
        return new MapData(
            $systemMap->getSx(),
            $systemMap->getSy(),
            $systemMap->getFieldId()
        );
    }

    private function hasUserColonyInSystem(User $user, int $systemId): bool
    {
        foreach ($user->getColonies() as $colony) {
            if ($colony->getStarsystemMap()->getSystem()->getId() === $systemId) {
                return true;
            }
        }

        return false;
    }

    private function showPmHref(StarSystemMap $map, User $user): bool
    {
        return
            $map->getColony() !== null
            && !$map->getColony()->isFree()
            && $map->getColony()->getUser()->getId() !== $user->getId()
        ;
    }

    /**
     * @return array<int, ColonyScan>
     */
    public function getColonyScanList(User $user, int $systemId): array
    {
        $alliance = $user->getAlliance();

        if ($alliance !== null) {
            $unfilteredScans = array_merge(...$alliance->getMembers()->map(
                fn(User $user) => $user->getColonyScans()->toArray()
            ));
        } else {
            $unfilteredScans = $user->getColonyScans()->toArray();
        }

        $filteredScans = array_filter(
            $unfilteredScans,
            fn(ColonyScan $scan): bool => $scan->getColony()->getSystem()->getId() === $systemId
        );

        $scansByColony = [];
        foreach ($filteredScans as $scan) {
            $colonyId = $scan->getColony()->getId();
            if (!isset($scansByColony[$colonyId])) {
                $scansByColony[$colonyId] = [];
            }
            $scansByColony[$colonyId][] = $scan;
        }

        $latestScans = [];
        foreach ($scansByColony as $scans) {
            usort($scans, fn($a, $b): int => $b->getDate() <=> $a->getDate());
            $latestScans[] = $scans[0];
        }

        return $latestScans;
    }

    /**
     * @param array<Map> $allMapFields
     * @return array{head_row: array<int>, fields: array<array{row: int, fields: array<array{cx: int, cy: int, style: string, icon_path: string, title: string}>}>}
     */
    private function prepareRegionMapData(
        array $allMapFields,
        int $minX,
        int $maxX,
        int $minY,
        int $maxY
    ): array {
        $headRow = range($minX, $maxX);

        $fieldMap = [];
        foreach ($allMapFields as $field) {
            $x = $field->getX();
            $y = $field->getY();
            $fieldMap["$x-$y"] = $field;
        }

        $rows = [];
        for ($y = $minY; $y <= $maxY; $y++) {
            $rowData = [
                'row' => $y,
                'fields' => []
            ];

            for ($x = $minX; $x <= $maxX; $x++) {
                $key = "$x-$y";
                $fieldData = [
                    'cx' => $x,
                    'cy' => $y,
                    'style' => '',
                    'icon_path' => '',
                    'title' => ''
                ];

                if (isset($fieldMap[$key])) {
                    $field = $fieldMap[$key];
                    $fieldType = $field->getFieldType();
                    $layer = $field->getLayer();

                    $border = $field->getBorder();
                    $fieldData['style'] = $border;

                    $title = [];
                    $title[] = $fieldType->getName();

                    if ($field->getSystem() !== null) {
                        $title[] = 'System: ' . $field->getSystem()->getName();
                    }

                    $fieldData['title'] = implode('\n', $title);

                    if ($layer !== null) {
                        if ($layer->isEncoded()) {
                            $encodedPath = $this->encodedMap->getEncodedMapPath(
                                $fieldType->getId(),
                                $layer
                            );
                            $fieldData['icon_path'] = sprintf('region/%s', $encodedPath);
                        } else {
                            $fieldData['icon_path'] = sprintf(
                                'region/%d/%d.png',
                                $layer->getId(),
                                $fieldType->getId()
                            );
                        }
                    }
                }
                $rowData['fields'][] = $fieldData;
            }

            $rows[] = $rowData;
        }

        return [
            'head_row' => $headRow,
            'fields' => $rows
        ];
    }
}
