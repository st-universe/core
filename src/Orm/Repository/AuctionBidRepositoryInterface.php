<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\AuctionBidInterface;

/**
 * @extends ObjectRepository<AuctionBid>
 *
 * @method null|AuctionBidInterface find(integer $auction_id)
 */
interface AuctionBidRepositoryInterface extends ObjectRepository
{
    public function prototype(): AuctionBidInterface;

    public function save(AuctionBidInterface $post): void;

    public function delete(AuctionBidInterface $post): void;
}
