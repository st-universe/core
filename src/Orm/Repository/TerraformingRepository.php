<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyClassRestriction;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\Terraforming;

/**
 * @extends EntityRepository<Terraforming>
 */
final class TerraformingRepository extends EntityRepository implements TerraformingRepositoryInterface
{
    #[Override]
    public function getBySourceFieldTypeAndUser(int $sourceFieldTypeId, int $userId, int $colonyClassId): array
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
                                    AND r.user_id = :userId))
                                                                AND NOT EXISTS (SELECT ccr.id FROM %s ccr
                            WHERE ccr.terraforming_id = t.id
                            AND ccr.colony_class_id = :colonyClassId)',
                Terraforming::class,
                Researched::class,
                ColonyClassRestriction::class
            )
        )->setParameters([
            'userId' => $userId,
            'sourceFieldTypeId' => $sourceFieldTypeId,
            'colonyClassId' => $colonyClassId
        ])->getResult();
    }
}