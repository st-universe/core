<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\OrionAuctionBid;
use Stu\Orm\Entity\User;

/** @extends EntityRepository<OrionAuctionBid> */
final class OrionAuctionBidRepository extends EntityRepository implements OrionAuctionBidRepositoryInterface
{
    public function prototype(): OrionAuctionBid
    {
        return new OrionAuctionBid();
    }

    public function save(OrionAuctionBid $bid): void
    {
        $this->getEntityManager()->persist($bid);
    }

    public function delete(OrionAuctionBid $bid): void
    {
        $this->getEntityManager()->remove($bid);
    }

    public function getHighestBid(OrionAuction $auction): ?OrionAuctionBid
    {
        return $this->findOneBy(['auction' => $auction], ['max_amount' => 'DESC', 'id' => 'ASC']);
    }

    /** @return list<OrionAuctionBid> */
    public function getByAuction(OrionAuction $auction): array
    {
        return $this->findBy(['auction' => $auction]);
    }

    /** @return list<OrionAuctionBid> */
    public function getByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }
}
