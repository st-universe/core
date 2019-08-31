<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BuildingUpgrade;
use Stu\Orm\Entity\Researched;

final class BuildingUpgradeRepository extends EntityRepository implements BuildingUpgradeRepositoryInterface
{
    public function getByBuilding(int $buildingId, int $userId): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t WHERE t.upgrade_from = :buildingId AND (
                    t.research_id = 0 OR t.research_id IN (SELECT r.research_id from %s r WHERE r.aktiv = 0 AND r.user_id = :userId)
                )',
                BuildingUpgrade::class,
                Researched::class
            )
        )
            ->setParameters(['userId' => $userId, 'buildingId' => $buildingId])
            ->getResult();
    }
}