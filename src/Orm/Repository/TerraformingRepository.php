<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\Terraforming;

final class TerraformingRepository extends EntityRepository implements TerraformingRepositoryInterface
{
    public function getBySourceFieldType(int $sourceFieldTypeId, int $userId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t
                 WHERE t.v_feld = :sourceFieldTypeId
                 AND (t.research_id IS NULL
                        OR EXISTS (SELECT r.id
                                    FROM %s r
                                    WHERE t.research_id = r.research_id
                                    AND t.user_id = :userId))',
                Terraforming::class,
                Researched::class
            )
        )->setParameters([
            'userId' => $userId,
            'sourceFieldTypeId' => $sourceFieldTypeId
        ])->getResult();
    }
}
