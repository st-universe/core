<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\Terraforming;

/**
 * @extends EntityRepository<Terraforming>
 */
final class TerraformingRepository extends EntityRepository implements TerraformingRepositoryInterface
{
    #[Override]
    public function getBySourceFieldTypeAndUser(int $sourceFieldTypeId, int $userId): array
    {
        if ($userId == UserEnum::USER_NOONE) {
            return $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT t FROM %s t
                 WHERE t.v_feld = :sourceFieldTypeId',
                    Terraforming::class
                )
            )->setParameters([
                'sourceFieldTypeId' => $sourceFieldTypeId
            ])->getResult();
        }

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t INDEX BY t.id
                 WHERE t.v_feld = :sourceFieldTypeId
                 AND (t.research_id IS NULL
                        OR EXISTS (SELECT r.id
                                    FROM %s r
                                    WHERE t.research_id = r.research_id
                                    AND r.finished > 0
                                    AND r.user_id = :userId))',
                Terraforming::class,
                Researched::class
            )
        )->setParameters([
            'userId' => $userId,
            'sourceFieldTypeId' => $sourceFieldTypeId
        ])->getResult();
    }
}
