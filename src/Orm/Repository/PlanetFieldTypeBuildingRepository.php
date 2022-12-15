<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\Researched;

final class PlanetFieldTypeBuildingRepository extends EntityRepository implements PlanetFieldTypeBuildingRepositoryInterface
{
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy(
            ['buildings_id' => $buildingId]
        );
    }

    public function getShowableFieldtypes(int $buildingId, int $userId): array
    {
        return $this->getEntityManager()->createQuery(

            sprintf(
                'SELECT fb.type FROM %s fb WHERE fb.buildings_id = :buildingId AND fb.view = TRUE AND (
                    fb.research_id is null OR fb.research_id IN (
                        SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.finished > 0
                    ))',
                PlanetFieldTypeBuilding::class,
                Researched::class
            )
        )
            ->setParameters([
                'userId' => $userId,
                'buildingId' => $buildingId
            ])
            ->getResult();
    }
}