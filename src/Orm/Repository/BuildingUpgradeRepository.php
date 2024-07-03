<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BuildingUpgrade;
use Stu\Orm\Entity\Researched;

/**
 * @extends EntityRepository<BuildingUpgrade>
 */
final class BuildingUpgradeRepository extends EntityRepository implements BuildingUpgradeRepositoryInterface
{
    #[Override]
    public function getByBuilding(int $buildingId, int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t WHERE t.upgrade_from = :buildingId AND (
                        t.research_id IS NULL OR t.research_id IN (SELECT r.research_id from %s r WHERE r.aktiv = :activeState AND r.user_id = :userId)
                    )',
                    BuildingUpgrade::class,
                    Researched::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'buildingId' => $buildingId,
                'activeState' => 0,
            ])
            ->getResult();
    }
}
