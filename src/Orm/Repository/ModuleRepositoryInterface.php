<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;

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
        ColonyInterface|ShipInterface $host,
        ShipModuleTypeEnum $moduleType,
        int $shipRumpId
    ): array;

    /**
     * @return array<ModuleInterface>
     */
    #[Override]
    public function getBySpecialTypeAndRumpAndRole(
        ColonyInterface|ShipInterface $host,
        ShipModuleTypeEnum $moduleType,
        int $shipRumpId,
        int $shipRumpRoleId
    ): array;


    /**
     * @param array<int> $moduleLevel
     *
     * @return array<ModuleInterface>
     */
    public function getByTypeColonyAndLevel(
        int $colonyId,
        ShipModuleTypeEnum $moduleType,
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
