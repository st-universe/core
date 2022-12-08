<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface DealsAuctionInterface
{

    public function getId(): int;

    public function getAuctionId(): ?int;

    public function setAuctionId(int $auction_id): DealsAuctionInterface;

    public function getUserId(): ?int;

    public function setUserId(int $user_id): DealsAuctionInterface;

    public function getActualAmount(): ?int;

    public function setActualAmount(int $actual_amount): DealsAuctionInterface;

    public function getMaxAmount(): ?int;

    public function setMaxAmount(int $max_amount): DealsAuctionInterface;

    public function getAuctionUser(): ?UserInterface;

    public function setAuctionUser(UserInterface $auctionuser): DealsAuctionInterface;

    public function getAuction(): ?DealsInterface;

    public function setAuction(DealsInterface $auctionid): DealsAuctionInterface;
}