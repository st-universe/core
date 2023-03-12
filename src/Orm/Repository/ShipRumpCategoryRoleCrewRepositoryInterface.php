<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;

/**
 * @extends ObjectRepository<ShipRumpCategoryRoleCrew>
 */
interface ShipRumpCategoryRoleCrewRepositoryInterface extends ObjectRepository
{
    public function getByShipRumpCategoryAndRole(
        int $shipRumpCategoryId,
        int $shipRumpRoleId
    ): ?ShipRumpCategoryRoleCrewInterface;
}
