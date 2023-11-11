<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Researched>
 */
final class ResearchedRepository extends EntityRepository implements ResearchedRepositoryInterface
{
    public function hasUserFinishedResearch(UserInterface $user, array $researchIds): bool
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

    public function getCurrentResearch(UserInterface $user): array
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

    public function getFor(int $researchId, int $userId): ?ResearchedInterface
    {
        return $this->findOneBy([
            'research_id' => $researchId,
            'user_id' => $userId,
        ]);
    }

    public function save(ResearchedInterface $researched): void
    {
        $em = $this->getEntityManager();

        $em->persist($researched);
        $em->flush();
    }

    public function delete(ResearchedInterface $researched): void
    {
        $em = $this->getEntityManager();

        $em->remove($researched);
        $em->flush();
    }

    public function prototype(): ResearchedInterface
    {
        return new Researched();
    }

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

    public function getResearchedPoints(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('points', 'points', 'integer');

        return $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT u.id as user_id,
                    (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id AND r.commodity_id = :lvl1) 
                    + (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id AND r.commodity_id = :lvl2) *2
                    + (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id AND r.commodity_id = :lvl3) *3
                    + (SELECT coalesce(sum(r.points), 0)
                        FROM stu_researched red JOIN stu_research r ON red.research_id = r.id
                        WHERE red.user_id = u.id AND r.commodity_id IN (:lvl4)) *4 AS points
                FROM stu_user u
                WHERE u.id >= :firstUserId
                ORDER BY points DESC',
                $rsm
            )
            ->setParameters([
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'lvl1' => CommodityTypeEnum::COMMODITY_RESEARCH_LVL1,
                'lvl2' => CommodityTypeEnum::COMMODITY_RESEARCH_LVL2,
                'lvl3' => CommodityTypeEnum::COMMODITY_RESEARCH_LVL3,
                'lvl4' => CommodityTypeEnum::COMMODITY_RESEARCH_LVL4
            ])
            ->getArrayResult();
    }
}
