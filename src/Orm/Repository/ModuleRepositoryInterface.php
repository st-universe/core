<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpRoleInterface;

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
    public function getBySpecialTypeColonyAndRump(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpId,
        int $shipRumpRoleId
    ): array;

    /**
     * @return array<ModuleInterface>
     */
    public function getBySpecialTypeShipAndRump(
        int $shipId,
        int $moduleTypeId, // 1 bis 9: ShipModuleTypeEnum
        int $shipRumpId,
        ShipRumpRoleInterface $shipRumpRole
    ): array;

    /**
     * @param array<int> $moduleLevel
     *
     * @return array<ModuleInterface>
     */
    public function getByTypeColonyAndLevel(
        int $colonyId,
        int $moduleTypeId,
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
