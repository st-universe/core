<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface AuctionBidInterface
{
    public function getId(): int;

    public function getAuctionId(): int;

    public function setAuctionId(int $auction_id): AuctionBidInterface;

    public function getUserId(): int;

    public function setUserId(int $user_id): AuctionBidInterface;

    public function getMaxAmount(): int;

    public function setMaxAmount(int $max_amount): AuctionBidInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): AuctionBidInterface;

    public function getAuction(): DealsInterface;

    public function setAuction(DealsInterface $auctionid): AuctionBidInterface;
}
