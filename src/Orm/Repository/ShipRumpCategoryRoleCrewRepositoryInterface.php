<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;

/**
 * @extends ObjectRepository<ShipRumpCategoryRoleCrew>
 */
interface ShipRumpCategoryRoleCrewRepositoryInterface extends ObjectRepository
{
    public function getByShipRumpCategoryAndRole(
        SpacecraftRumpCategoryEnum $shipRumpCategory,
        SpacecraftRumpRoleEnum $shipRumpRole
    ): ?ShipRumpCategoryRoleCrewInterface;
}
