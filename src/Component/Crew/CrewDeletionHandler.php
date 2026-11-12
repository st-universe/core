<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Orm\Entity\Crew;
use Stu\Orm\Repository\OrionAuctionBidRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionRepositoryInterface;

final class CrewDeletionHandler
{
    public function __construct(
        private OrionAuctionRepositoryInterface $auctions,
        private OrionAuctionBidRepositoryInterface $bids
    ) {}

    public function delete(Crew $crew): void
    {
        foreach ($this->auctions->findBy(['crew' => $crew]) as $auction) {
            foreach ($this->bids->getByAuction($auction) as $bid) {
                $this->bids->delete($bid);
            }
            $this->auctions->delete($auction);
        }
    }
}
