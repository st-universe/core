<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AuctionBid;

/**
 * @extends EntityRepository<AuctionBid>
 */
final class AuctionBidRepository extends EntityRepository implements AuctionBidRepositoryInterface
{
    #[\Override]
    public function prototype(): AuctionBid
    {
        return new AuctionBid();
    }

    #[\Override]
    public function save(AuctionBid $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(AuctionBid $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush(); //TODO really neccessary?
    }
}
