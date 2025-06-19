<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrewInterface;

/**
 * @extends EntityRepository<ShipRumpCategoryRoleCrew>
 */
final class ShipRumpCategoryRoleCrewRepository extends EntityRepository implements ShipRumpCategoryRoleCrewRepositoryInterface
{
    #[Override]
    public function getByShipRumpCategoryAndRole(
        SpacecraftRumpCategoryEnum $shipRumpCategory,
        SpacecraftRumpRoleEnum $shipRumpRole
    ): ?ShipRumpCategoryRoleCrewInterface {
        return $this->findOneBy([
            'rump_category_id' => $shipRumpCategory->value,
            'rump_role_id' => $shipRumpRole->value,
        ]);
    }
}
