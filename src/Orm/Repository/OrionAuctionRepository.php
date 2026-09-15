<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\User;

/** @extends EntityRepository<OrionAuction> */
final class OrionAuctionRepository extends EntityRepository implements OrionAuctionRepositoryInterface
{
    public function prototype(): OrionAuction
    {
        return new OrionAuction();
    }

    public function save(OrionAuction $auction): void
    {
        $this->getEntityManager()->persist($auction);
    }

    public function delete(OrionAuction $auction): void
    {
        $this->getEntityManager()->remove($auction);
    }

    /** @return list<OrionAuction> */
    public function getActive(): array
    {
        return $this->createQueryBuilder('auction')
            ->where('auction.start <= :time')
            ->andWhere('auction.end > :time')
            ->andWhere('auction.completed_at IS NULL')
            ->setParameter('time', time())
            ->orderBy('auction.end', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<OrionAuction> */
    public function getExpired(int $time): array
    {
        return $this->createQueryBuilder('auction')
            ->where('auction.end <= :time')
            ->andWhere('auction.completed_at IS NULL')
            ->setParameter('time', $time)
            ->getQuery()
            ->getResult();
    }

    /** @return list<OrionAuction> */
    public function getHistorySince(int $time): array
    {
        return $this->createQueryBuilder('auction')
            ->where('auction.completed_at >= :time')
            ->setParameter('time', $time)
            ->orderBy('auction.completed_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<OrionAuction> */
    public function getByWinner(User $user): array
    {
        return $this->createQueryBuilder('auction')
            ->where('auction.winner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
