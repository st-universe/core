<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleInterface;

/**
 * @extends ObjectRepository<Module>
 *
 * @method null|ModuleInterface find(integer $id)
 */
interface ModuleRepositoryInterface extends ObjectRepository
{
    /**
     * @return ModuleInterface[]
     */
    public function getBySpecialTypeColonyAndRump(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpId,
        int $shipRumpRoleId
    ): array;

    /**
     * @return ModuleInterface[]
     */
    public function getBySpecialTypeShipAndRump(
        int $shipId,
        int $moduleTypeId, // 1 bis 9: ShipModuleTypeEnum
        int $shipRumpId,
        int $shipRumpRoleId
    ): array;

    /**
     * @return ModuleInterface[]
     */
    public function getByTypeColonyAndLevel(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array;

    /**
     * @return ModuleInterface[]
     */
    public function getByTypeAndLevel(
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): iterable;

    public function getBySpecialTypeIds(array $specialTypeIds): iterable;
}
