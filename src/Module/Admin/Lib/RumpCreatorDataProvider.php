<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\ShipRumpCategory;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpModuleSpecial;
use Stu\Orm\Entity\ShipRumpRole;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRepositoryInterface;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCostRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRoleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpSpecialRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRump3DModelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class RumpCreatorDataProvider
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private readonly ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private readonly SpacecraftRump3DModelRepositoryInterface $spacecraftRump3DModelRepository,
        private readonly ShipRumpCostRepositoryInterface $shipRumpCostRepository,
        private readonly ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        private readonly ShipRumpSpecialRepositoryInterface $shipRumpSpecialRepository,
        private readonly ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository,
        private readonly ShipRumpCategoryRepositoryInterface $shipRumpCategoryRepository,
        private readonly ShipRumpRoleRepositoryInterface $shipRumpRoleRepository,
        private readonly CommodityRepositoryInterface $commodityRepository,
        private readonly DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private readonly DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly BuildingRepositoryInterface $buildingRepository,
        private readonly ModuleSpecialRepositoryInterface $moduleSpecialRepository
    ) {}

    public function getRumps(): RumpCreatorData
    {
        $rumps = $this->spacecraftRumpRepository->findAll();

        usort(
            $rumps,
            fn (SpacecraftRump $a, SpacecraftRump $b): int => [mb_strtolower($a->getName()), $a->getId()] <=> [mb_strtolower($b->getName()), $b->getId()]
        );

        return new RumpCreatorData(array_map(
            fn (SpacecraftRump $rump): array => [
                'id' => $rump->getId(),
                'name' => $rump->getName()
            ],
            $rumps
        ));
    }

    public function getTemplates(): RumpCreatorData
    {
        $baseValues = [];
        foreach ($this->entityManager->getRepository(SpacecraftRumpBaseValues::class)->findAll() as $baseValue) {
            $baseValues[$baseValue->getRump()->getId()] = $baseValue;
        }

        $moduleLevels = [];
        foreach ($this->shipRumpModuleLevelRepository->findAll() as $moduleLevel) {
            $values = [];
            foreach (SpacecraftModuleTypeEnum::cases() as $type) {
                if ($type->isSpecialSystemType()) {
                    continue;
                }
                $values[$type->value] = [
                    'min' => $moduleLevel->getMinimumLevel($type),
                    'default' => $moduleLevel->getDefaultLevel($type),
                    'max' => $moduleLevel->getMaximumLevel($type),
                    'mandatory' => $moduleLevel->isMandatory($type)
                ];
            }
            $moduleLevels[$moduleLevel->getRump()->getId()] = $values;
        }

        $models = [];
        foreach ($this->spacecraftRump3DModelRepository->findAll() as $model) {
            $models[$model->getRumpId()] = $model;
        }

        $costs = [];
        foreach ($this->shipRumpCostRepository->findAll() as $cost) {
            $costs[$cost->getRumpId()][] = $cost;
        }

        $moduleSpecialIds = [];
        foreach ($this->entityManager->getRepository(ShipRumpModuleSpecial::class)->findAll() as $moduleSpecial) {
            $moduleSpecialIds[$moduleSpecial->getRumpId()][] = $moduleSpecial->getModuleSpecialId();
        }

        $buildingFunctionIds = [];
        foreach ($this->shipRumpBuildingFunctionRepository->findAll() as $buildingFunction) {
            $buildingFunctionIds[$buildingFunction->getRumpId()][] = $buildingFunction->getBuildingFunction()->value;
        }

        $specialAbilityIds = [];
        foreach ($this->shipRumpSpecialRepository->findAll() as $specialAbility) {
            $specialAbilityIds[$specialAbility->getRumpId()][] = $specialAbility->getSpecialId();
        }

        $colonizationBuildings = [];
        foreach ($this->shipRumpColonizationBuildingRepository->findAll() as $colonizationBuilding) {
            $colonizationBuildings[$colonizationBuilding->getRumpId()] = $colonizationBuilding;
        }
        $templates = [];

        foreach ($this->spacecraftRumpRepository->findAll() as $rump) {
            $rumpId = $rump->getId();
            $databaseEntry = $rump->getDatabaseId() === null
                ? null
                : $this->databaseEntryRepository->findOneBy(['id' => $rump->getDatabaseId()]);
            $baseValue = $baseValues[$rumpId] ?? null;
            $model = $models[$rumpId] ?? null;

            $templates[$rumpId] = [
                'id' => $rumpId,
                'name' => $rump->getName(),
                'core' => [
                    'category_id' => $rump->getCategoryId()->value,
                    'role_id' => $rump->getRoleId()?->value,
                    'base_torpedo_storage' => $rump->getBaseTorpedoStorage(),
                    'phaser_volleys' => $rump->getPhaserVolleys(),
                    'phaser_hull_damage_factor' => $rump->getPhaserHullDamageFactor(),
                    'phaser_shield_damage_factor' => $rump->getPhaserShieldDamageFactor(),
                    'torpedo_level' => $rump->getTorpedoLevel(),
                    'torpedo_volleys' => $rump->getTorpedoVolleys(),
                    'is_buildable' => $rump->getIsBuildable(),
                    'is_npc' => $rump->getIsNpc(),
                    'eps_cost' => $rump->getEpsCost(),
                    'storage' => $rump->getStorage(),
                    'slots' => $rump->getDockingSlots(),
                    'buildtime' => $rump->getBuildtime(),
                    'needed_workbees' => $rump->getNeededWorkbees(),
                    'sort' => $rump->getSort(),
                    'commodity_id' => $rump->getCommodityId(),
                    'faction_id' => $rump->getFactionId(),
                    'flight_ecost' => $rump->getFlightEcost(),
                    'beam_factor' => $rump->getBeamFactor(),
                    'shuttle_slots' => $rump->getShuttleSlots(),
                    'tractor_mass' => $rump->getTractorMass(),
                    'tractor_payload' => $rump->getTractorPayload(),
                    'prestige' => $rump->getPrestige(),
                    'npc_buildable' => $rump->getNpcBuildable() === null ? '' : (int) $rump->getNpcBuildable()
                ],
                'baseValues' => $baseValue === null ? null : [
                    'evade_chance' => $baseValue->getEvadeChance(),
                    'hit_chance' => $baseValue->getHitChance(),
                    'module_level' => $baseValue->getModuleLevel(),
                    'base_crew' => $baseValue->getBaseCrew(),
                    'max_crew' => $baseValue->getMaxCrew(),
                    'base_eps' => $baseValue->getBaseEps(),
                    'base_reactor' => $baseValue->getBaseReactor(),
                    'base_hull' => $baseValue->getBaseHull(),
                    'base_shield' => $baseValue->getBaseShield(),
                    'base_damage' => $baseValue->getBaseDamage(),
                    'base_sensor_range' => $baseValue->getBaseSensorRange(),
                    'base_warpdrive' => $baseValue->getBaseWarpDrive(),
                    'special_slots' => $baseValue->getSpecialSlots()
                ],
                'moduleLevels' => $moduleLevels[$rumpId] ?? null,
                'model3d' => $model === null ? null : [
                    'width' => $model->getWidth(),
                    'height' => $model->getHeight(),
                    'rotation' => $model->getRotation()
                ],
                'costs' => array_map(
                    fn (ShipRumpCost $cost): array => [
                        'commodityId' => $cost->getCommodityId(),
                        'amount' => $cost->getAmount()
                    ],
                    $costs[$rumpId] ?? []
                ),
                'moduleSpecialIds' => $moduleSpecialIds[$rumpId] ?? [],
                'buildingFunctionIds' => $buildingFunctionIds[$rumpId] ?? [],
                'specialAbilityIds' => $specialAbilityIds[$rumpId] ?? [],
                'colonizationBuildingId' => ($colonizationBuildings[$rumpId] ?? null)?->getBuildingId(),
                'databaseEntry' => $databaseEntry === null ? null : [
                    'id' => $databaseEntry->getId(),
                    'description' => $databaseEntry->getDescription(),
                    'data' => $databaseEntry->getData(),
                    'categoryId' => $databaseEntry->getCategoryId()
                ]
            ];
        }

        return new RumpCreatorData($templates);
    }

    public function getOptions(): RumpCreatorData
    {
        return new RumpCreatorData([
            'CATEGORIES' => $this->getCategories(),
            'ROLES' => $this->getRoles(),
            'COMMODITIES' => $this->getCommodities(),
            'DATABASE_ENTRIES' => $this->getDatabaseEntries(),
            'DATABASE_CATEGORIES' => $this->getDatabaseCategories(),
            'FACTIONS' => $this->getFactions(),
            'BUILDINGS' => $this->getBuildings(),
            'MODULE_SPECIALS' => $this->getModuleSpecials(),
            'BUILDING_FUNCTIONS' => array_map(
                fn (BuildingFunctionEnum $function): array => [
                    'id' => $function->value,
                    'name' => str_replace('_', ' ', $function->name)
                ],
                BuildingFunctionEnum::cases()
            ),
            'MODULE_TYPES' => array_map(
                fn (SpacecraftModuleTypeEnum $type): array => [
                    'id' => $type->value,
                    'name' => $type->getDescription()
                ],
                array_values(array_filter(
                    SpacecraftModuleTypeEnum::getModuleSelectorOrder(),
                    fn (SpacecraftModuleTypeEnum $type): bool => !$type->isSpecialSystemType()
                ))
            )
        ]);
    }

    private function getCategories(): RumpCreatorData
    {
        $categories = $this->shipRumpCategoryRepository->findAll();

        usort($categories, fn (ShipRumpCategory $a, ShipRumpCategory $b): int => $a->getName() <=> $b->getName());

        return new RumpCreatorData(array_map(
            fn (ShipRumpCategory $category): array => [
                'id' => $category->getId()->value,
                'name' => $category->getName()
            ],
            $categories
        ));
    }

    private function getRoles(): RumpCreatorData
    {
        $roles = $this->shipRumpRoleRepository->findAll();

        usort($roles, fn (ShipRumpRole $a, ShipRumpRole $b): int => $a->getName() <=> $b->getName());

        return new RumpCreatorData(array_map(
            fn (ShipRumpRole $role): array => [
                'id' => $role->getId()->value,
                'name' => $role->getName()
            ],
            $roles
        ));
    }

    private function getCommodities(): RumpCreatorData
    {
        $commodities = $this->commodityRepository->findAll();

        usort($commodities, fn (Commodity $a, Commodity $b): int => $a->getName() <=> $b->getName());

        return new RumpCreatorData(array_map(
            fn (Commodity $commodity): array => [
                'id' => $commodity->getId(),
                'name' => $commodity->getName()
            ],
            $commodities
        ));
    }

    private function getDatabaseEntries(): RumpCreatorData
    {
        $entries = array_values(array_filter(
            $this->databaseEntryRepository->findAll(),
            fn (DatabaseEntry $entry): bool => $entry->getTypeId() === DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP
        ));

        usort(
            $entries,
            fn (DatabaseEntry $a, DatabaseEntry $b): int => [
                mb_strtolower($a->getCategory()->getDescription()),
                $a->getSort(),
                mb_strtolower($a->getDescription()),
                $a->getId()
            ] <=> [
                mb_strtolower($b->getCategory()->getDescription()),
                $b->getSort(),
                mb_strtolower($b->getDescription()),
                $b->getId()
            ]
        );

        return new RumpCreatorData(array_map(
            fn (DatabaseEntry $entry): array => [
                'id' => $entry->getId(),
                'name' => $entry->getDescription(),
                'categoryId' => $entry->getCategoryId(),
                'categoryName' => $entry->getCategory()->getDescription(),
                'sort' => $entry->getSort()
            ],
            $entries
        ));
    }

    private function getDatabaseCategories(): RumpCreatorData
    {
        $categories = array_values(array_filter(
            $this->databaseCategoryRepository->findAll(),
            fn (DatabaseCategory $category): bool => $category->getType() === DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP
        ));

        usort($categories, fn (DatabaseCategory $a, DatabaseCategory $b): int => $a->getDescription() <=> $b->getDescription());

        return new RumpCreatorData(array_map(
            fn (DatabaseCategory $category): array => [
                'id' => $category->getId(),
                'name' => $category->getDescription()
            ],
            $categories
        ));
    }

    private function getFactions(): RumpCreatorData
    {
        $factions = $this->factionRepository->findAll();

        usort($factions, fn (Faction $a, Faction $b): int => $a->getName() <=> $b->getName());

        return new RumpCreatorData(array_map(
            fn (Faction $faction): array => [
                'id' => $faction->getId(),
                'name' => $faction->getName()
            ],
            $factions
        ));
    }

    private function getBuildings(): RumpCreatorData
    {
        $buildings = $this->buildingRepository->findAll();

        usort($buildings, fn (Building $a, Building $b): int => $a->getName() <=> $b->getName());

        return new RumpCreatorData(array_map(
            fn (Building $building): array => [
                'id' => $building->getId(),
                'name' => $building->getName()
            ],
            $buildings
        ));
    }

    private function getModuleSpecials(): RumpCreatorData
    {
        $specials = $this->moduleSpecialRepository->getAllForRumpCreator();

        return new RumpCreatorData(array_map(
            fn (array $special): array => [
                'id' => $special['id'],
                'name' => sprintf(
                    'Modul %d – %s',
                    $special['module_id'],
                    ModuleSpecialAbilityEnum::tryFrom($special['special_id'])?->getDescription() ?? sprintf('Spezial-ID %d', $special['special_id'])
                )
            ],
            $specials
        ));
    }

}
