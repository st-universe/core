<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleInterface;

interface ModuleRepositoryInterface extends ObjectRepository
{
    /**
     * @return ModuleInterface[]
     */
    public function getBySpecialTypeAndRump(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpId,
        int $shipRumpRoleId
    ): array;

    /**
     * @return ModuleInterface[]
     */
    public function getByTypeAndLevel(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array;
}