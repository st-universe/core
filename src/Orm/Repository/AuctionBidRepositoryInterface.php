<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AuctionBid;

/**
 * @extends ObjectRepository<AuctionBid>
 *
 * @method null|AuctionBid find(integer $auction_id)
 */
interface AuctionBidRepositoryInterface extends ObjectRepository
{
    public function prototype(): AuctionBid;

    public function save(AuctionBid $post): void;

    public function delete(AuctionBid $post): void;
}
