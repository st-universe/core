<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\AuctionBidInterface;

/**
 * @extends EntityRepository<AuctionBid>
 */
final class AuctionBidRepository extends EntityRepository implements AuctionBidRepositoryInterface
{
    #[Override]
    public function prototype(): AuctionBidInterface
    {
        return new AuctionBid();
    }

    #[Override]
    public function save(AuctionBidInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(AuctionBidInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush(); //TODO really neccessary?
    }
}
