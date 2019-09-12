<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingInterface;

/**
 * @method null|BuildingInterface find(integer $id)
 */
interface BuildingRepositoryInterface extends ObjectRepository
{
    public function getByColonyAndUserAndBuildMenu(
        int $colonyId,
        int $userId,
        int $buildMenu,
        int $offset
    ): iterable;
}