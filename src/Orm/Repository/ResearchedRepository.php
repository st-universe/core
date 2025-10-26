<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Researched>
 */
final class ResearchedRepository extends EntityRepository implements ResearchedRepositoryInterface
{
    #[\Override]
    public function hasUserFinishedResearch(User $user, array $researchIds): bool
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(t.id) FROM %s t WHERE t.research_id IN (:researchIds) AND t.user_id = :userId AND t.finished > 0',
                    Researched::class,
                )
            )
            ->setParameters(['userId' => $user, 'researchIds' => $researchIds])
            ->getSingleScalarResult() > 0;
    }

    #[\Override]
    public function getListByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t
                    WHERE t.user_id = :userId
                    AND (t.finished > 0 OR t.aktiv > 0)
                    ORDER BY t.id DESC',
                    Researched::class,
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[\Override]
    public function getFinishedListByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t WHERE t.user_id = :userId AND t.finished > 0',
                    Researched::class,
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[\Override]
    public function getCurrentResearch(User $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r
                    WHERE r.user = :user
                    AND r.aktiv > 0
                    ORDER BY r.id asc',
                    Researched::class
                )
            )
            ->setParameter('user', $user)
            ->getResult();
    }

    #[\Override]
    public function getFor(int $researchId, int $userId): ?Researched
    {
        return $this->findOneBy([
            'research_id' => $researchId,
            'user_id' => $userId,
        ]);
    }

    #[\Override]
    public function save(Researched $researched): void
    {
        $em = $this->getEntityManager();

        $em->persist($researched);
        $em->flush(); //TODO really neccessary?
    }

    #[\Override]
    public function delete(Researched $researched): void
    {
        $em = $this->getEntityManager();

        $em->remove($researched);
        $em->flush();
    }

    #[\Override]
    public function prototype(): Researched
    {
        return new Researched();
    }

    #[\Override]
    public function truncateForUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s t WHERE t.user_id = :userId',
                    Researched::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }

    #[\Override]
    public function getResearchedPoints(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('points', 'points', 'integer');
        $rsm->addScalarResult('timestamp', 'timestamp', 'integer');

        return $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT u.id as user_id,
                    (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id
                        AND red.finished > 0
                        AND r.commodity_id = :lvl1) 
                    + (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id
                        AND red.finished > 0
                        AND r.commodity_id = :lvl2) *2
                    + (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id
                        AND red.finished > 0
                        AND r.commodity_id = :lvl3) *3
                    + (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id
                        AND red.finished > 0
                        AND r.commodity_id IN (:lvl4)) *4 AS points,
                    (SELECT MAX(red.finished)
                        FROM stu_researched red
                        WHERE red.user_id = u.id) AS timestamp
                FROM stu_user u
                WHERE u.id >= :firstUserId
                ORDER BY points DESC, timestamp ASC',
                $rsm
            )
            ->setParameters([
                'firstUserId' => UserConstants::USER_FIRST_ID,
                'lvl1' => CommodityTypeConstants::COMMODITY_RESEARCH_LVL1,
                'lvl2' => CommodityTypeConstants::COMMODITY_RESEARCH_LVL2,
                'lvl3' => CommodityTypeConstants::COMMODITY_RESEARCH_LVL3,
                'lvl4' => CommodityTypeConstants::COMMODITY_RESEARCH_LVL4
            ])
            ->getArrayResult();
    }
}
