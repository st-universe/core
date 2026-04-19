<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Award;

/**
 * @extends EntityRepository<Award>
 */
final class AwardRepository extends EntityRepository implements AwardRepositoryInterface
{
    private const int NPC_AWARD_START_ID = 1000;

    #[\Override]
    public function getNextNpcAwardId(): int
    {
        $maxId = (int) $this
            ->createQueryBuilder('a')
            ->select('COALESCE(MAX(a.id), 0)')
            ->where('a.id >= :startId')
            ->setParameter('startId', self::NPC_AWARD_START_ID)
            ->getQuery()
            ->getSingleScalarResult();

        return max(self::NPC_AWARD_START_ID, $maxId + 1);
    }

    /**
     * @return array<int, Award>
     */
    #[\Override]
    public function getNpcAwards(): array
    {
        return $this
            ->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->addSelect('u')
            ->where('a.is_npc = :isNpc')
            ->setParameter('isNpc', true)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function save(Award $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    #[\Override]
    public function delete(Award $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush(); //TODO really neccessary?
    }

    #[\Override]
    public function prototype(): Award
    {
        return new Award();
    }
}
