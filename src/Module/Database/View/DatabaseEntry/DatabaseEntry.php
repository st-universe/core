<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DatabaseEntry;

use Override;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Exception\AccessViolationException;
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
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
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
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private ShipRepositoryInterface $shipRepository,
        private PlanetGeneratorInterface $planetGenerator
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $entryId = $this->databaseEntryRequest->getEntryId();
        $categoryId = $this->databaseEntryRequest->getCategoryId();

        if (!$this->databaseUserRepository->exists($userId, $entryId)) {
            throw new AccessViolationException(sprintf(_('userId %d tried to open databaseEntryId %d, but has not discovered it yet!'), $userId, $entryId));
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
                self::VIEW_IDENTIFIER,
                $categoryId,
                $entryId,
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

    private function addSpecialVars(GameControllerInterface $game, DatabaseEntryInterface $entry): void
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
                            $energy = $energyModule
                                ->getType()
                                ->getModuleRumpWrapperCallable()($rump, $plan)
                                ->getValue($energyModule);

                            $game->setTemplateVar('EPS', $energy);
                        }


                        $sensormodule = $mods->filter(
                            fn($mod): bool => $mod->getModule()->getType() === SpacecraftModuleTypeEnum::SENSOR
                        )->first();

                        if ($sensormodule !== false) {
                            $sensorModule = $sensormodule->getModule();
                            $sensor = $sensorModule
                                ->getType()
                                ->getModuleRumpWrapperCallable()($rump, $plan)
                                ->getValue($sensorModule);

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
            $unfilteredScans = array_merge(...$alliance->getMembers()->map(fn(UserInterface $user) => $user->getColonyScans()->toArray()));
        } else {
            $unfilteredScans = $user->getColonyScans()->toArray();
        }

        $filteredScans = array_filter(
            $unfilteredScans,
            fn(ColonyScanInterface $scan): bool => $scan->getColony()->getSystem()->getId() === $systemId
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
}
