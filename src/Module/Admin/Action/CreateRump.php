<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Admin\Lib\RumpCreatorData;
use Stu\Module\Admin\View\RumpCreator\ShowRumpCreator;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpModuleSpecial;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRump3DModel;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRoleRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Throwable;

final class CreateRump implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_RUMP';
    public const string UPDATE_ACTION_IDENTIFIER = 'B_UPDATE_RUMP';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private readonly DatabaseEntryRepositoryInterface $databaseEntryRepository,
        private readonly DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        private readonly DatabaseTypeRepositoryInterface $databaseTypeRepository,
        private readonly ShipRumpCategoryRepositoryInterface $shipRumpCategoryRepository,
        private readonly ShipRumpRoleRepositoryInterface $shipRumpRoleRepository,
        private readonly CommodityRepositoryInterface $commodityRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly BuildingRepositoryInterface $buildingRepository,
        private readonly ModuleSpecialRepositoryInterface $moduleSpecialRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowRumpCreator::VIEW_IDENTIFIER);

        $isUpdate = request::postString(self::UPDATE_ACTION_IDENTIFIER) !== false;
        $editRumpId = $isUpdate
            ? $this->readOptionalInteger($game, 'edit_rump_id', 'Zu bearbeitender Rumpf', 1, PHP_INT_MAX)
            : null;
        if ($editRumpId === false) {
            return;
        }
        if ($isUpdate && $editRumpId === null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('Bitte einen bestehenden Rumpf zum Bearbeiten auswählen'));
            return;
        }

        $data = $this->readData($game, $editRumpId);
        if ($data === null) {
            return;
        }

        try {
            $this->entityManager->wrapInTransaction(function () use ($data, $editRumpId): void {
                if ($editRumpId === null) {
                    $this->persist($data);
                } else {
                    $this->update($editRumpId, $data);
                }
            });
        } catch (Throwable) {
            $this->entityManager->clear();
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('Der Rumpf konnte nicht gespeichert werden'));
            return;
        }

        $game->getInfo()->addInformationf(
            $editRumpId === null
                ? _('Der Rumpf %s mit der ID %d wurde erstellt')
                : _('Der Rumpf %s mit der ID %d wurde aktualisiert'),
            $data->get('name'),
            $data->get('id')
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function readData(GameControllerInterface $game, ?int $editRumpId): ?RumpCreatorData
    {
        $id = $this->readInteger($game, 'rump_id', 'Rumpf-ID', 1, PHP_INT_MAX);
        if ($id === false) {
            return null;
        }

        $name = $this->readString($game, 'rump_name', 'Rumpfname', 255);
        if ($name === false) {
            return null;
        }

        if ($editRumpId === null && $this->spacecraftRumpRepository->find($id) !== null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('Die Rumpf-ID ist bereits vergeben'));
            return null;
        }
        if ($editRumpId !== null && $id !== $editRumpId) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('Die Rumpf-ID darf beim Bearbeiten nicht geändert werden'));
            return null;
        }

        $categoryId = $this->readInteger($game, 'category_id', 'Kategorie', 1, PHP_INT_MAX);
        if ($categoryId === false) {
            return null;
        }
        if ($this->shipRumpCategoryRepository->find($categoryId) === null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('Kategorie mit der ID %d existiert nicht'), $categoryId));
            return null;
        }

        $roleId = $this->readOptionalInteger($game, 'role_id', 'Rolle', 1, PHP_INT_MAX);
        if ($roleId === false) {
            return null;
        }
        if ($roleId !== null && $this->shipRumpRoleRepository->find($roleId) === null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('Rolle mit der ID %d existiert nicht'), $roleId));
            return null;
        }

        $commodityId = $this->readOptionalInteger($game, 'commodity_id', 'Waren-ID', 1, PHP_INT_MAX);
        if ($commodityId === false) {
            return null;
        }
        if ($commodityId !== null && $this->commodityRepository->find($commodityId) === null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('Ware mit der ID %d existiert nicht'), $commodityId));
            return null;
        }

        $factionId = $this->readOptionalInteger($game, 'faction_id', 'Fraktions-ID', 1, PHP_INT_MAX);
        if ($factionId === false) {
            return null;
        }
        if ($factionId !== null && $this->factionRepository->find($factionId) === null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('Fraktion mit der ID %d existiert nicht'), $factionId));
            return null;
        }

        $databaseId = $this->readOptionalInteger($game, 'database_id', 'Datenbankeintrag', 1, PHP_INT_MAX);
        if ($databaseId === false) {
            return null;
        }
        if ($databaseId !== null) {
            $databaseEntry = $this->databaseEntryRepository->find($databaseId);
            if ($databaseEntry === null || $databaseEntry->getTypeId() !== DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Der Datenbankeintrag ist kein Rumpf-Eintrag'));
                return null;
            }
        }

        $createDatabaseEntry = request::postString('create_database_entry') !== false;
        if ($createDatabaseEntry && $databaseId !== null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('Bitte einen vorhandenen Datenbankeintrag auswählen oder einen neuen anlegen'));
            return null;
        }

        $databaseEntry = null;
        if ($createDatabaseEntry) {
            $databaseEntryId = $this->readOptionalInteger($game, 'database_entry_id', 'Datenbank-ID', 1, PHP_INT_MAX);
            if ($databaseEntryId === false) {
                return null;
            }
            if ($databaseEntryId !== null && $this->databaseEntryRepository->find($databaseEntryId) !== null) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Die Datenbank-ID ist bereits vergeben'));
                return null;
            }

            $databaseEntry = $this->readDatabaseEntryData($game);
            if ($databaseEntry === null) {
                return null;
            }
            if ($databaseEntryId !== null) {
                $databaseEntry = new RumpCreatorData([
                    'id' => $databaseEntryId,
                    'description' => $databaseEntry->get('description'),
                    'data' => $databaseEntry->get('data'),
                    'category_id' => $databaseEntry->get('category_id'),
                    'insert_before_id' => $databaseEntry->get('insert_before_id')
                ]);
            }
        }

        $databaseEntryUpdate = null;
        if (request::postString('update_database_entry') !== false) {
            if ($createDatabaseEntry || $databaseId === null) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Zum Bearbeiten muss ein vorhandener Datenbankeintrag verknüpft sein'));
                return null;
            }

            $databaseEntryUpdate = $this->readDatabaseEntryData($game);
            if ($databaseEntryUpdate === null) {
                return null;
            }
        }

        $npcBuildableValue = request::postString('npc_buildable');
        if (!in_array($npcBuildableValue, [false, '', '0', '1'], true)) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('NPC-Baubarkeit ist ungültig'));
            return null;
        }
        $npcBuildable = match ($npcBuildableValue) {
            false, '' => null,
            '0' => false,
            '1' => true
        };

        $core = [
            'id' => $id,
            'category_id' => $categoryId,
            'role_id' => $roleId,
            'name' => $name,
            'is_buildable' => request::postString('is_buildable') !== false,
            'is_npc' => request::postString('is_npc') !== false,
            'database_id' => $databaseId,
            'commodity_id' => $commodityId,
            'faction_id' => $factionId,
            'npc_buildable' => $npcBuildable
        ];
        foreach ([
            'base_torpedo_storage' => ['Basis-Torpedolager', 0, 32767],
            'phaser_volleys' => ['Energiewaffensalven', 0, 32767],
            'phaser_hull_damage_factor' => ['Energiewaffen-Hüllenschaden', 0, 32767],
            'phaser_shield_damage_factor' => ['Energiewaffen-Schildschaden', 0, 32767],
            'torpedo_level' => ['Torpedolevel', 0, 32767],
            'torpedo_volleys' => ['Torpedosalven', 0, 32767],
            'eps_cost' => ['EPS-Kosten', 0, 32767],
            'storage' => ['Lagerkapazität', 0, PHP_INT_MAX],
            'slots' => ['Dockplätze', 0, 32767],
            'buildtime' => ['Bauzeit', 0, PHP_INT_MAX],
            'sort' => ['Sortierung', 0, 32767],
            'flight_ecost' => ['Flugkosten', 0, 32767],
            'beam_factor' => ['Beamfaktor', 0, 32767],
            'shuttle_slots' => ['Shuttleslots', 0, 32767],
            'tractor_mass' => ['Traktormasse', 1, PHP_INT_MAX],
            'tractor_payload' => ['Traktor-Zuladung', 0, PHP_INT_MAX],
            'prestige' => ['Prestige', PHP_INT_MIN, PHP_INT_MAX]
        ] as $field => [$label, $minimum, $maximum]) {
            $value = $this->readInteger($game, $field, $label, $minimum, $maximum);
            if ($value === false) {
                return null;
            }
            $core[$field] = $value;
        }
        $neededWorkbees = $this->readOptionalInteger($game, 'needed_workbees', 'Benötigte Workbees', 0, 32767);
        if ($neededWorkbees === false) {
            return null;
        }
        $core['needed_workbees'] = $neededWorkbees;

        $baseValues = ['rump_id' => $id];
        foreach ([
            'evade_chance' => ['Ausweichchance', 0, 32767],
            'hit_chance' => ['Trefferchance', 0, 32767],
            'module_level' => ['Modullevel', 0, 32767],
            'base_crew' => ['Basis-Crew', 0, 32767],
            'max_crew' => ['Maximale Crew', 0, 32767],
            'base_eps' => ['Basis-EPS', 0, 32767],
            'base_reactor' => ['Basis-Reaktor', 0, 32767],
            'base_hull' => ['Basis-Hülle', 0, PHP_INT_MAX],
            'base_shield' => ['Basis-Schilde', 0, PHP_INT_MAX],
            'base_damage' => ['Basis-Schaden', 0, 32767],
            'base_sensor_range' => ['Basis-Sensorreichweite', 0, 32767],
            'base_warpdrive' => ['Basis-Warpantrieb', 0, PHP_INT_MAX],
            'special_slots' => ['Spezialslots', 0, 32767]
        ] as $field => [$label, $minimum, $maximum]) {
            $value = $this->readInteger($game, $field, $label, $minimum, $maximum);
            if ($value === false) {
                return null;
            }
            $baseValues[$field] = $value;
        }
        $moduleLevels = request::postString('create_module_levels') === false
            ? null
            : $this->readModuleLevels($game);
        if ($moduleLevels === null && request::postString('create_module_levels') !== false) {
            return null;
        }
        $model3d = request::postString('create_model_3d') === false
            ? null
            : $this->readModel3dData($game, $id);
        if ($model3d === false) {
            return null;
        }
        $costs = $this->readCosts($game);
        if ($costs === null) {
            return null;
        }
        $moduleSpecialIds = $this->readModuleSpecialIds($game);
        if ($moduleSpecialIds === null) {
            return null;
        }
        $buildingFunctionIds = $this->readBuildingFunctionIds($game);
        if ($buildingFunctionIds === null) {
            return null;
        }
        $specialAbilityIds = $this->readSpecialAbilityIds($game);
        if ($specialAbilityIds === null) {
            return null;
        }
        $colonizationBuildingId = $this->readColonizationBuildingId($game);
        if ($colonizationBuildingId === false) {
            return null;
        }
        return new RumpCreatorData([
            ...$core,
            'base_values' => new RumpCreatorData($baseValues),
            'database_entry' => $databaseEntry,
            'database_entry_update' => $databaseEntryUpdate,
            'module_levels' => $moduleLevels,
            'model_3d' => $model3d,
            'costs' => $costs,
            'module_special_ids' => $moduleSpecialIds,
            'building_function_ids' => $buildingFunctionIds,
            'special_ability_ids' => $specialAbilityIds,
            'colonization_building_id' => $colonizationBuildingId
        ]);
    }

    private function persist(RumpCreatorData $data): void
    {
        $rump = $this->createRump($data);
        $this->synchronizeDatabaseEntry($rump, $data);
        $this->spacecraftRumpRepository->save($rump);
        $this->entityManager->persist($this->createBaseValues($rump, $data->get('base_values')));
        $this->persistConfigurations($rump, $data);

        $this->entityManager->flush();
    }

    private function update(int $rumpId, RumpCreatorData $data): void
    {
        $rump = $this->spacecraftRumpRepository->find($rumpId)
            ?? throw new LogicException('Rump does not exist');

        $this->applyRumpValues($rump, $data);
        $this->synchronizeDatabaseEntry($rump, $data);

        $baseValues = $this->entityManager->find(SpacecraftRumpBaseValues::class, $rumpId);
        if ($baseValues === null) {
            $this->entityManager->persist($this->createBaseValues($rump, $data->get('base_values')));
        } else {
            $this->applyBaseValues($baseValues, $data->get('base_values'));
        }

        $this->removeRumpConfigurations($rump);
        $this->persistConfigurations($rump, $data);

        $this->spacecraftRumpRepository->save($rump);
        $this->entityManager->flush();
    }

    private function synchronizeDatabaseEntry(SpacecraftRump $rump, RumpCreatorData $data): void
    {
        if ($data->get('database_entry') !== null) {
            $rump->setDatabaseEntry($this->createDatabaseEntry($data));
        } elseif ($data->get('database_id') !== null) {
            $databaseEntry = $this->databaseEntryRepository->find($data->get('database_id'));
            if ($databaseEntry === null) {
                throw new LogicException('Database entry does not exist');
            }
            $rump->setDatabaseEntry($databaseEntry);
        } else {
            $rump->setDatabaseEntry(null);
        }

        if ($data->get('database_entry_update') === null) {
            return;
        }

        $databaseEntry = $rump->getDatabaseEntry();
        if ($databaseEntry === null || $databaseEntry->getObjectId() !== $rump->getId()) {
            throw new LogicException('Database entry does not belong to rump');
        }

        $this->updateDatabaseEntry($databaseEntry, $data->get('database_entry_update'));
    }

    private function updateDatabaseEntry(DatabaseEntry $databaseEntry, RumpCreatorData $data): void
    {
        $category = $this->databaseCategoryRepository->find($data->get('category_id'));
        if ($category === null) {
            throw new LogicException('Database category does not exist');
        }

        if (
            $databaseEntry->getCategoryId() === $data->get('category_id')
            && $databaseEntry->getId() === $data->get('insert_before_id')
        ) {
            $sort = $databaseEntry->getSort();
        } else {
            $this->closeDatabaseEntryGap(
                $databaseEntry->getCategoryId(),
                $databaseEntry->getSort(),
                $databaseEntry->getId()
            );
            $sort = $this->getDatabaseEntrySort(
                $data->get('category_id'),
                $data->get('insert_before_id'),
                $databaseEntry->getId()
            );
        }

        $databaseEntry
            ->setCategory($category)
            ->setDescription($data->get('description'))
            ->setData($data->get('data'))
            ->setSort($sort);
    }

    private function closeDatabaseEntryGap(int $categoryId, int $sort, int $excludedEntryId): void
    {
        $expectedSort = $sort + 1;
        foreach ($this->getDatabaseEntriesByCategory($categoryId, $excludedEntryId)->all() as $entry) {
            if ($entry->getSort() < $expectedSort) {
                continue;
            }
            if ($entry->getSort() !== $expectedSort) {
                break;
            }

            $entry->setSort($entry->getSort() - 1);
            $expectedSort++;
        }
    }

    private function removeRumpConfigurations(SpacecraftRump $rump): void
    {
        $rumpId = $rump->getId();
        foreach (
            [
                ShipRumpCost::class,
                ShipRumpModuleSpecial::class,
                ShipRumpBuildingFunction::class,
                ShipRumpSpecial::class,
                ShipRumpColonizationBuilding::class
            ] as $entityClass
        ) {
            foreach ($this->entityManager->getRepository($entityClass)->findBy(['rump_id' => $rumpId]) as $entity) {
                $this->entityManager->remove($entity);
            }
        }
    }

    private function persistConfigurations(SpacecraftRump $rump, RumpCreatorData $data): void
    {
        $moduleLevel = $this->entityManager->find(ShipRumpModuleLevel::class, $rump->getId());
        if ($data->get('module_levels') === null) {
            if ($moduleLevel !== null) {
                $this->entityManager->remove($moduleLevel);
            }
        } else {
            $moduleLevel ??= (new ShipRumpModuleLevel())->setRump($rump);
            foreach ($data->get('module_levels')->all() as $typeId => $values) {
                $type = SpacecraftModuleTypeEnum::from((int) $typeId);
                $moduleLevel
                    ->setValue($type, ShipRumpModuleLevel::MIN_LEVEL_KEY, $values['min'])
                    ->setValue($type, ShipRumpModuleLevel::DEFAULT_LEVEL_KEY, $values['default'])
                    ->setValue($type, ShipRumpModuleLevel::MAX_LEVEL_KEY, $values['max'])
                    ->setValue($type, ShipRumpModuleLevel::MANDATORY_KEY, $values['mandatory']);
            }
            $this->entityManager->persist($moduleLevel);
        }

        $model = $this->entityManager->find(SpacecraftRump3DModel::class, $rump->getId());
        if ($data->get('model_3d') === null) {
            if ($model !== null) {
                $this->entityManager->remove($model);
            }
        } else {
            $modelData = $data->get('model_3d');
            if (!$modelData instanceof RumpCreatorData) {
                throw new LogicException('3D model configuration does not exist');
            }
            $model ??= (new SpacecraftRump3DModel())->setRump($rump);
            $model
                ->setWidth($modelData->get('width'))
                ->setHeight($modelData->get('height'))
                ->setRotation($modelData->get('rotation'));
            $this->entityManager->persist($model);
        }

        foreach ($data->get('costs')->all() as $cost) {
            $commodity = $this->commodityRepository->find($cost['commodity_id']);
            if ($commodity === null) {
                throw new LogicException('Commodity does not exist');
            }
            $this->entityManager->persist(
                (new ShipRumpCost())
                    ->setRumpId($rump->getId())
                    ->setSpacecraftRump($rump)
                    ->setCommodityId($cost['commodity_id'])
                    ->setCommodity($commodity)
                    ->setAmount($cost['count'])
            );
        }

        foreach ($data->get('module_special_ids')->all() as $moduleSpecialId) {
            $this->entityManager->persist(
                (new ShipRumpModuleSpecial())
                    ->setRumpId($rump->getId())
                    ->setModuleSpecialId($moduleSpecialId)
            );
        }

        foreach ($data->get('building_function_ids')->all() as $buildingFunctionId) {
            $this->entityManager->persist(
                (new ShipRumpBuildingFunction())
                    ->setRumpId($rump->getId())
                    ->setBuildingFunction(BuildingFunctionEnum::from($buildingFunctionId))
            );
        }

        foreach ($data->get('special_ability_ids')->all() as $specialAbilityId) {
            $this->entityManager->persist(
                (new ShipRumpSpecial())
                    ->setRumpId($rump->getId())
                    ->setSpacecraftRump($rump)
                    ->setSpecialId($specialAbilityId)
            );
        }

        if ($data->get('colonization_building_id') !== null) {
            $this->entityManager->persist(
                (new ShipRumpColonizationBuilding())
                    ->setRumpId($rump->getId())
                    ->setBuildingId($data->get('colonization_building_id'))
            );
        }
    }

    private function createRump(RumpCreatorData $data): SpacecraftRump
    {
        $rump = $this->spacecraftRumpRepository->prototype()
            ->setId($data->get('id'));

        $this->applyRumpValues($rump, $data);

        return $rump;
    }

    private function applyRumpValues(SpacecraftRump $rump, RumpCreatorData $data): void
    {
        $category = $this->shipRumpCategoryRepository->find($data->get('category_id'));
        $role = $data->get('role_id') === null
            ? null
            : $this->shipRumpRoleRepository->find($data->get('role_id'));
        $commodity = $data->get('commodity_id') === null
            ? null
            : $this->commodityRepository->find($data->get('commodity_id'));

        if ($category === null || ($data->get('role_id') !== null && $role === null) || ($data->get('commodity_id') !== null && $commodity === null)) {
            throw new LogicException('Rump configuration does not exist');
        }

        $rump
            ->setCategoryId(SpacecraftRumpCategoryEnum::from($data->get('category_id')))
            ->setRoleId($data->get('role_id') === null ? null : SpacecraftRumpRoleEnum::from($data->get('role_id')))
            ->setShipRumpCategory($category)
            ->setShipRumpRole($role)
            ->setBaseTorpedoStorage($data->get('base_torpedo_storage'))
            ->setPhaserVolleys($data->get('phaser_volleys'))
            ->setPhaserHullDamageFactor($data->get('phaser_hull_damage_factor'))
            ->setPhaserShieldDamageFactor($data->get('phaser_shield_damage_factor'))
            ->setTorpedoLevel($data->get('torpedo_level'))
            ->setTorpedoVolleys($data->get('torpedo_volleys'))
            ->setName($data->get('name'))
            ->setIsBuildable($data->get('is_buildable'))
            ->setIsNpc($data->get('is_npc'))
            ->setEpsCost($data->get('eps_cost'))
            ->setStorage($data->get('storage'))
            ->setDockingSlots($data->get('slots'))
            ->setBuildtime($data->get('buildtime'))
            ->setNeededWorkbees($data->get('needed_workbees'))
            ->setSort($data->get('sort'))
            ->setCommodityId($data->get('commodity_id'))
            ->setCommodity($commodity)
            ->setFactionId($data->get('faction_id'))
            ->setFlightEcost($data->get('flight_ecost'))
            ->setBeamFactor($data->get('beam_factor'))
            ->setShuttleSlots($data->get('shuttle_slots'))
            ->setTractorMass($data->get('tractor_mass'))
            ->setTractorPayload($data->get('tractor_payload'))
            ->setPrestige($data->get('prestige'))
            ->setNpcBuildable($data->get('npc_buildable'));
    }

    private function createBaseValues(SpacecraftRump $rump, RumpCreatorData $data): SpacecraftRumpBaseValues
    {
        $baseValues = (new SpacecraftRumpBaseValues())
            ->setRump($rump);

        $this->applyBaseValues($baseValues, $data);

        return $baseValues;
    }

    private function applyBaseValues(SpacecraftRumpBaseValues $baseValues, RumpCreatorData $data): void
    {
        $baseValues
            ->setEvadeChance($data->get('evade_chance'))
            ->setHitChance($data->get('hit_chance'))
            ->setModuleLevel($data->get('module_level'))
            ->setBaseCrew($data->get('base_crew'))
            ->setMaxCrew($data->get('max_crew'))
            ->setBaseEps($data->get('base_eps'))
            ->setBaseReactor($data->get('base_reactor'))
            ->setBaseHull($data->get('base_hull'))
            ->setBaseShield($data->get('base_shield'))
            ->setBaseDamage($data->get('base_damage'))
            ->setBaseSensorRange($data->get('base_sensor_range'))
            ->setBaseWarpDrive($data->get('base_warpdrive'))
            ->setSpecialSlots($data->get('special_slots'));
    }

    private function createDatabaseEntry(RumpCreatorData $data): DatabaseEntry
    {
        $databaseEntryData = $data->get('database_entry');
        if (!$databaseEntryData instanceof RumpCreatorData) {
            throw new LogicException('Database entry configuration does not exist');
        }

        $category = $this->databaseCategoryRepository->find($databaseEntryData->get('category_id'));
        $type = $this->databaseTypeRepository->find(DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP);
        if ($category === null || $type === null) {
            throw new LogicException('Database category or type does not exist');
        }

        $sort = $this->getDatabaseEntrySort(
            $databaseEntryData->get('category_id'),
            $databaseEntryData->get('insert_before_id')
        );
        $databaseEntry = $this->databaseEntryRepository->prototype()
            ->setCategory($category)
            ->setDescription($databaseEntryData->get('description'))
            ->setData($databaseEntryData->get('data'))
            ->setSort($sort)
            ->setObjectId($data->get('id'))
            ->setTypeObject($type);

        if ($databaseEntryData->get('id') === null) {
            $this->databaseEntryRepository->save($databaseEntry);

            return $databaseEntry;
        }

        $this->databaseEntryRepository->saveWithManualId($databaseEntry, $databaseEntryData->get('id'));

        return $this->databaseEntryRepository->find($databaseEntryData->get('id'))
            ?? throw new LogicException('Database entry could not be loaded');
    }

    private function getDatabaseEntrySort(int $categoryId, ?int $insertBeforeId, ?int $excludedEntryId = null): int
    {
        $entries = $this->getDatabaseEntriesByCategory($categoryId, $excludedEntryId)->all();

        if ($insertBeforeId === null) {
            $highestSort = null;
            foreach ($entries as $entry) {
                $highestSort = $highestSort === null
                    ? $entry->getSort()
                    : max($highestSort, $entry->getSort());
            }

            return $highestSort === null ? 0 : $highestSort + 1;
        }

        $insertBeforeEntry = $this->databaseEntryRepository->find($insertBeforeId);
        if ($insertBeforeEntry === null) {
            throw new LogicException('Database entry does not exist');
        }

        $sort = $insertBeforeEntry->getSort();
        $expectedSort = $sort;
        foreach ($entries as $entry) {
            if ($entry->getSort() < $expectedSort) {
                continue;
            }
            if ($entry->getSort() !== $expectedSort) {
                break;
            }

            $entry->setSort($entry->getSort() + 1);
            $expectedSort++;
        }

        return $sort;
    }

    private function getDatabaseEntriesByCategory(int $categoryId, ?int $excludedEntryId = null): RumpCreatorData
    {
        $entries = array_values(array_filter(
            $this->databaseEntryRepository->getByCategoryId($categoryId),
            fn(DatabaseEntry $entry): bool => $entry->getTypeId() === DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP
                && $entry->getId() !== $excludedEntryId
        ));
        usort($entries, fn(DatabaseEntry $a, DatabaseEntry $b): int => [$a->getSort(), $a->getId()] <=> [$b->getSort(), $b->getId()]);

        return new RumpCreatorData($entries);
    }

    private function readModel3dData(GameControllerInterface $game, int $rumpId): RumpCreatorData|false
    {
        $model3d = ['rump_id' => $rumpId];
        foreach ([
            'width' => ['model_3d_width', '3D-Modellbreite'],
            'height' => ['model_3d_height', '3D-Modellhöhe'],
            'rotation' => ['model_3d_rotation', '3D-Modellrotation']
        ] as $field => [$requestField, $label]) {
            $value = $this->readInteger($game, $requestField, $label, 0, PHP_INT_MAX);
            if ($value === false) {
                return false;
            }
            $model3d[$field] = $value;
        }

        return new RumpCreatorData($model3d);
    }

    private function readDatabaseEntryData(GameControllerInterface $game): ?RumpCreatorData
    {
        $description = $this->readString($game, 'database_entry_description', 'Datenbank-Beschreibung', 255);
        if ($description === false) {
            return null;
        }
        $categoryId = $this->readInteger($game, 'database_entry_category_id', 'Datenbank-Kategorie', 1, PHP_INT_MAX);
        if ($categoryId === false) {
            return null;
        }
        $insertBeforeId = $this->readOptionalInteger($game, 'database_entry_before_id', 'Datenbank-Einfügeposition', 1, PHP_INT_MAX);
        if ($insertBeforeId === false) {
            return null;
        }
        $databaseEntry = [
            'description' => $description,
            'data' => (string) request::postString('database_entry_data'),
            'category_id' => $categoryId,
            'insert_before_id' => $insertBeforeId
        ];
        $databaseCategory = $this->databaseCategoryRepository->find($databaseEntry['category_id']);
        if ($databaseCategory === null || $databaseCategory->getType() !== DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(_('Die Datenbank-Kategorie ist nicht für Rümpfe vorgesehen'));
            return null;
        }

        if ($databaseEntry['insert_before_id'] !== null) {
            $insertBeforeEntry = $this->databaseEntryRepository->find($databaseEntry['insert_before_id']);
            if (
                $insertBeforeEntry === null
                || $insertBeforeEntry->getTypeId() !== DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP
                || $insertBeforeEntry->getCategoryId() !== $databaseEntry['category_id']
            ) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Die gewählte Einfügeposition gehört nicht zur Datenbank-Kategorie'));
                return null;
            }
        }

        return new RumpCreatorData($databaseEntry);
    }

    private function preserveFormValues(GameControllerInterface $game): void
    {
        $game->setTemplateVar('RUMP_CREATOR_FORM_VALUES', request::postvars());
    }

    private function readModuleLevels(GameControllerInterface $game): ?RumpCreatorData
    {
        $levels = [];

        foreach (SpacecraftModuleTypeEnum::cases() as $type) {
            if ($type->isSpecialSystemType()) {
                continue;
            }

            $minimum = $this->readInteger($game, 'module_min_' . $type->value, $type->getDescription() . ': Minimum', 0, 32767);
            $default = $this->readInteger($game, 'module_default_' . $type->value, $type->getDescription() . ': Standard', 0, 32767);
            $maximum = $this->readInteger($game, 'module_max_' . $type->value, $type->getDescription() . ': Maximum', 0, 32767);
            if ($minimum === false || $default === false || $maximum === false) {
                return null;
            }
            if ($minimum > $maximum) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(sprintf(_('Der minimale Modullevel für %s darf nicht größer als der maximale sein'), $type->getDescription()));
                return null;
            }

            $levels[$type->value] = [
                'min' => $minimum,
                'default' => $default,
                'max' => $maximum,
                'mandatory' => request::postString('module_mandatory_' . $type->value) !== false
            ];
        }

        return new RumpCreatorData($levels);
    }

    private function readCosts(GameControllerInterface $game): ?RumpCreatorData
    {
        $commodityIds = request::postArray('cost_commodity_ids');
        $amounts = request::postArray('cost_amounts');
        $costs = [];
        $rowCount = max(count($commodityIds), count($amounts));

        for ($index = 0; $index < $rowCount; $index++) {
            $commodityValue = trim((string) ($commodityIds[$index] ?? ''));
            $amountValue = trim((string) ($amounts[$index] ?? ''));
            if ($commodityValue === '' && $amountValue === '') {
                continue;
            }
            if ($commodityValue === '' || $amountValue === '') {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Jede Baukosten-Zeile benötigt Ware und Menge'));
                return null;
            }

            $commodityId = $this->parseInteger($game, $commodityValue, 'Baukosten-Ware', 1, PHP_INT_MAX);
            if ($commodityId === false) {
                return null;
            }
            if ($this->commodityRepository->find($commodityId) === null) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(sprintf(_('Baukosten-Ware mit der ID %d existiert nicht'), $commodityId));
                return null;
            }
            $amount = $this->parseInteger($game, $amountValue, 'Baukosten-Menge', 1, PHP_INT_MAX);
            if ($amount === false) {
                return null;
            }
            $costs[$commodityId] = [
                'commodity_id' => $commodityId,
                'count' => $amount
            ];
        }

        return new RumpCreatorData(array_values($costs));
    }

    private function readModuleSpecialIds(GameControllerInterface $game): ?RumpCreatorData
    {
        $idList = $this->readIdList($game, 'module_special_ids', 'Spezialmodul');
        if ($idList === null) {
            return null;
        }
        $ids = $idList->all();
        foreach ($ids as $id) {
            if ($this->moduleSpecialRepository->find($id) === null) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(sprintf(_('Spezialmodul mit der ID %d existiert nicht'), $id));
                return null;
            }
        }

        return new RumpCreatorData($ids);
    }

    private function readBuildingFunctionIds(GameControllerInterface $game): ?RumpCreatorData
    {
        $idList = $this->readIdList($game, 'building_function_ids', 'Gebäudefunktion');
        if ($idList === null) {
            return null;
        }
        $ids = $idList->all();
        foreach ($ids as $id) {
            if (BuildingFunctionEnum::tryFrom($id) === null) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Eine ausgewählte Gebäudefunktion existiert nicht'));
                return null;
            }
        }

        return new RumpCreatorData($ids);
    }

    private function readSpecialAbilityIds(GameControllerInterface $game): ?RumpCreatorData
    {
        $idList = $this->readIdList($game, 'special_ability_ids', 'Sonderfähigkeit');
        if ($idList === null) {
            return null;
        }
        $ids = $idList->all();
        foreach ($ids as $id) {
            if ($id !== SpacecraftRump::SPECIAL_ABILITY_COLONIZE) {
                $this->preserveFormValues($game);
                $game->getInfo()->addInformation(_('Eine ausgewählte Sonderfähigkeit existiert nicht'));
                return null;
            }
        }

        return new RumpCreatorData($ids);
    }

    private function readColonizationBuildingId(GameControllerInterface $game): int|false|null
    {
        $buildingId = $this->readOptionalInteger($game, 'colonization_building_id', 'Kolonisationsgebäude', 1, PHP_INT_MAX);
        if ($buildingId === false) {
            return false;
        }
        if ($buildingId !== null && $this->buildingRepository->find($buildingId) === null) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('Kolonisationsgebäude mit der ID %d existiert nicht'), $buildingId));
            return false;
        }

        return $buildingId;
    }

    private function readIdList(GameControllerInterface $game, string $field, string $label): ?RumpCreatorData
    {
        $ids = [];
        foreach (request::postArray($field) as $value) {
            $id = $this->parseInteger($game, (string) $value, $label, 1, PHP_INT_MAX);
            if ($id === false) {
                return null;
            }
            $ids[$id] = $id;
        }

        return new RumpCreatorData(array_values($ids));
    }

    private function readInteger(GameControllerInterface $game, string $field, string $label, int $minimum, int $maximum): int|false
    {
        return $this->parseInteger($game, (string) request::postString($field), $label, $minimum, $maximum);
    }

    private function readOptionalInteger(GameControllerInterface $game, string $field, string $label, int $minimum, int $maximum): int|false|null
    {
        $value = trim((string) request::postString($field));
        if ($value === '') {
            return null;
        }

        return $this->parseInteger($game, $value, $label, $minimum, $maximum);
    }

    private function readString(GameControllerInterface $game, string $field, string $label, int $maximumLength): string|false
    {
        $value = trim((string) request::postString($field));
        if ($value === '' || mb_strlen($value) > $maximumLength) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('%s muss zwischen 1 und %d Zeichen lang sein'), $label, $maximumLength));
            return false;
        }

        return $value;
    }

    private function parseInteger(GameControllerInterface $game, string $value, string $label, int $minimum, int $maximum): int|false
    {
        if (!preg_match('/^-?\\d+$/', $value)) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('%s muss eine ganze Zahl sein'), $label));
            return false;
        }

        $integer = (int) $value;
        if ($integer < $minimum || $integer > $maximum) {
            $this->preserveFormValues($game);
            $game->getInfo()->addInformation(sprintf(_('%s muss zwischen %d und %d liegen'), $label, $minimum, $maximum));
            return false;
        }

        return $integer;
    }
}
