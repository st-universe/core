<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\OrionAuctionBid;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<OrionAuctionBid>
 */
interface OrionAuctionBidRepositoryInterface extends ObjectRepository
{
    public function prototype(): OrionAuctionBid;

    public function save(OrionAuctionBid $bid): void;

    public function delete(OrionAuctionBid $bid): void;

    public function getHighestBid(OrionAuction $auction): ?OrionAuctionBid;

    /** @return list<OrionAuctionBid> */
    public function getByAuction(OrionAuction $auction): array;

    /** @return list<OrionAuctionBid> */
    public function getByUser(User $user): array;
}
