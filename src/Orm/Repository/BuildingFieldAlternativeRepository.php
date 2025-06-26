<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BuildingFieldAlternative;
use Stu\Orm\Entity\Researched;

/**
 * @extends EntityRepository<BuildingFieldAlternative>
 */
final class BuildingFieldAlternativeRepository extends EntityRepository implements BuildingFieldAlternativeRepositoryInterface
{
    #[Override]
    public function getByBuildingAndFieldType(int $buildingId, int $fieldType): ?BuildingFieldAlternative
    {
        return $this->findOneBy([
            'buildings_id' => $buildingId,
            'fieldtype' => $fieldType
        ]);
    }

    #[Override]
    public function getByBuildingIdAndResearchedByUser(int $buildingId, int $userId): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT b FROM %s b WHERE b.buildings_id = :buildingId AND (
                    b.research_id is null OR b.research_id IN (
                        SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.finished > 0
                    ))',
                    BuildingFieldAlternative::class,
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
