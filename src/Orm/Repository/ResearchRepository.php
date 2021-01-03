<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;

final class ResearchRepository extends EntityRepository implements ResearchRepositoryInterface
{

    public function getAvailableResearch(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT t FROM %s t WHERE t.id NOT IN (
                    SELECT r.research_id from %s r WHERE r.user_id = :userId
                )',
                Research::class,
                Researched::class,
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

        return $this->getEntityManager()->createNativeQuery(
                'SELECT r.id, r.name FROM stu_research r
                WHERE CAST(r.id AS TEXT) LIKE :factionId
                OR CAST(r.id AS TEXT) LIKE \'%%0\'',
                $rsm
        )
            ->setParameter('factionId', sprintf('%%%d', $factionId))
            ->getResult();
    }

    public function getPlanetColonyLimitByUser(UserInterface $user): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(r.upper_planetlimit) FROM %s r WHERE r.id IN (
                    SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.aktiv = :activeState
                )',
                Research::class,
                Researched::class
            )
        )->setParameters([
            'userId' => $user,
            'activeState' => 0,
        ])->getSingleScalarResult();
    }

    public function getMoonColonyLimitByUser(UserInterface $user): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(r.upper_moonlimit) FROM %s r WHERE r.id IN (
                    SELECT ru.research_id FROM %s ru WHERE ru.user_id = :userId AND ru.aktiv = :activeState
                )',
                Research::class,
                Researched::class
            )
        )->setParameters([
            'userId' => $user,
            'activeState' => 0
        ])->getSingleScalarResult();
    }

    public function save(ResearchInterface $research): void
    {
        $em = $this->getEntityManager();

        $em->persist($research);
        $em->flush();
    }
}
