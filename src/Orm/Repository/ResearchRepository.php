<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Research\ResearchEnum;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Research>
 */
final class ResearchRepository extends EntityRepository implements ResearchRepositoryInterface
{
    public function getAvailableResearch(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r
                    JOIN %s c
                    WITH r.commodity_id = c.id
                    WHERE r.id NOT IN (
                        SELECT red.research_id from %s red WHERE red.user_id = :userId
                    )
                    ORDER BY c.sort ASC, r.id ASC',
                    Research::class,
                    Commodity::class,
                    Researched::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    /**
     * Retrieves all tech entries for a faction. It relys on some fancy id magic, so consider this a temporary solution
     */
    public function getForFaction(int $factionId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Research::class, 'r');
        $rsm->addFieldResult('r', 'id', 'id');
        $rsm->addFieldResult('r', 'name', 'name');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT r.id, r.name FROM stu_research r
                    WHERE CAST(r.id AS TEXT) LIKE :factionId
                    OR CAST(r.id AS TEXT) LIKE \'%%0\'',
                $rsm
            )
            ->setParameter('factionId', sprintf('%%%d', $factionId))
            ->getResult();
    }

    public function getColonyTypeLimitByUser(UserInterface $user, int $colonyType): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(r.upper_limit_colony_amount) FROM %s r
                WHERE r.upper_limit_colony_type = :colonyType
                AND r.id IN (
                    SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.aktiv = :activeState
                )',
                Research::class,
                Researched::class
            )
        )->setParameters([
            'userId' => $user,
            'activeState' => 0,
            'colonyType' => $colonyType
        ])->getSingleScalarResult();
    }

    public function getPossibleResearchByParent(int $researchId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r
                    FROM %s r
                    WHERE r.id IN (
                        SELECT rd.research_id from %s rd
                        WHERE rd.depends_on = :researchId
                        AND rd.mode != :modeExclude
                    )',
                    Research::class,
                    ResearchDependency::class,
                )
            )
            ->setParameters([
                'researchId' => $researchId,
                'modeExclude' => ResearchEnum::RESEARCH_MODE_EXCLUDE
            ])
            ->getResult();
    }

    public function save(ResearchInterface $research): void
    {
        $em = $this->getEntityManager();

        $em->persist($research);
    }
}
