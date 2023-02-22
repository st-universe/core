<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\AuctionBidInterface;

/**
 * @extends EntityRepository<AuctionBid>
 */
final class AuctionBidRepository extends EntityRepository implements AuctionBidRepositoryInterface
{
    public function prototype(): AuctionBidInterface
    {
        return new AuctionBid();
    }

    public function save(AuctionBidInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(AuctionBidInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }
}
