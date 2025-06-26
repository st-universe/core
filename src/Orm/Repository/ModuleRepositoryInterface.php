<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

/**
 * @extends ObjectRepository<Module>
 *
 * @method null|Module find(integer $id)
 */
interface ModuleRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<Module>
     */
    public function getBySpecialTypeAndRump(
        Colony|Spacecraft $host,
        SpacecraftModuleTypeEnum $moduleType,
        int $rumpId
    ): array;

    /**
     * @return array<Module>
     */
    public function getBySpecialTypeAndRumpAndRole(
        Colony|Ship $host,
        SpacecraftModuleTypeEnum $moduleType,
        int $rumpId,
        int $shipRumpRoleId
    ): array;


    /**
     * @param array<int> $moduleLevel
     *
     * @return array<Module>
     */
    public function getByTypeColonyAndLevel(
        int $colonyId,
        SpacecraftModuleTypeEnum $moduleType,
        SpacecraftRumpRoleEnum $shipRumpRole,
        array $moduleLevel
    ): array;

    /**
     * @param array<int> $moduleLevel
     *
     * @return array<Module>
     */
    public function getByTypeAndLevel(
        int $moduleTypeId,
        SpacecraftRumpRoleEnum $shipRumpRole,
        array $moduleLevel
    ): iterable;

    /**
     * @param array<int> $specialTypeIds
     *
     * @return iterable<Module>
     */
    public function getBySpecialTypeIds(array $specialTypeIds): iterable;
}
