<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\Researched;

final class ResearchRepository extends EntityRepository implements ResearchRepositoryInterface
{

    public function getAvailableResearch(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t WHERE t.id NOT IN (
                    SELECT r.research_id from %s r WHERE r.user_id = %d
                )',
                Research::class,
                Researched::class,
                $userId
            )
        )->getResult();
    }

    /**
     * Retrieves all tech entries for a faction. It relys on some fancy id magic, so consider this a temporary solution
     */
    public function getForFaction(int $factionId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t WHERE t.id LIKE \'%%%d\' OR t.id LIKE \'%%0\'',
                Research::class,
                $factionId
            )
        )->getResult();
    }
}