<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

/**
 * @extends ObjectRepository<Module>
 *
 * @method null|ModuleInterface find(integer $id)
 */
interface ModuleRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<ModuleInterface>
     */
    public function getBySpecialTypeAndRump(
        ColonyInterface|SpacecraftInterface $host,
        SpacecraftModuleTypeEnum $moduleType,
        int $rumpId
    ): array;

    /**
     * @return array<ModuleInterface>
     */
    public function getBySpecialTypeAndRumpAndRole(
        ColonyInterface|ShipInterface $host,
        SpacecraftModuleTypeEnum $moduleType,
        int $rumpId,
        int $shipRumpRoleId
    ): array;


    /**
     * @param array<int> $moduleLevel
     *
     * @return array<ModuleInterface>
     */
    public function getByTypeColonyAndLevel(
        int $colonyId,
        SpacecraftModuleTypeEnum $moduleType,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array;

    /**
     * @param array<int> $moduleLevel
     *
     * @return array<ModuleInterface>
     */
    public function getByTypeAndLevel(
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): iterable;

    /**
     * @param array<int> $specialTypeIds
     *
     * @return iterable<ModuleInterface>
     */
    public function getBySpecialTypeIds(array $specialTypeIds): iterable;
}
