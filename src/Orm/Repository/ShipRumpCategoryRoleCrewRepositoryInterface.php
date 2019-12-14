<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;

interface ShipRumpCategoryRoleCrewRepositoryInterface extends ObjectRepository
{
    public function getByShipRumpCategoryAndRole(
        int $shipRumpCategoryId,
        int $shipRumpRoleId
    ): ?ShipRumpCategoryRoleCrewInterface;
}