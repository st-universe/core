<?php

// @todo activate strict typing
declare(strict_types=0);

namespace Stu\Module\Trade\Lib;

use Stu\Lib\TradePostStorageWrapper;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;
use TradeOffer;
use TradePostData;
use TradeStorage;

final class TradeAccountTal implements TradeAccountTalInterface
{
    private $tradeLicenseRepository;

    private $tradeTransferRepository;

    private $tradePost;

    private $userId;

    private $storage;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransferRepositoryInterface $tradeTransferRepository,
        TradePostData $tradePost,
        int $userId
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransferRepository = $tradeTransferRepository;
        $this->tradePost = $tradePost;
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    public function getShip(): \Ship
    {
        return $this->tradePost->getShip();
    }

    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    public function getStorage(): TradePostStorageWrapper
    {
        if ($this->storage === null) {
            $this->storage = TradeStorage::getStorageByTradepostUser($this->tradePost->getId(), $this->userId);
        }
        return $this->storage;
    }

    public function getOfferStorage(): array
    {
        return TradeOffer::getStorageByTradepostUser($this->tradePost->getId(), $this->userId);
    }

    public function getTradeNetwork(): int
    {
        return $this->tradePost->getTradeNetwork();
    }

    public function getFreeTransferCapacity(): int
    {
        return $this->tradePost->getTransferCapacity() - $this->tradeTransferRepository->getSumByPostAndUser(
            (int) $this->tradePost->getId(),
            $this->userId
        );
    }

    public function getTransferCapacity(): int
    {
        return $this->tradePost->getTransferCapacity();
    }

    public function isOverStorage(): bool
    {
        return $this->getStorage()->getStorageSum() > $this->tradePost->getStorage();
    }

    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }

    public function getLicenseCount(): int
    {
        return $this->tradeLicenseRepository->getAmountByTradePost((int)$this->tradePost->getId());
    }

    public function getFreeStorage(): int
    {
        return max(0, $this->tradePost->getStorage() - $this->getStorage()->getStorageSum());
    }

    public function getTradeLicenseCosts(): int
    {
        return $this->tradePost->calculateLicenceCost();
    }

    public function getTradeLicenseCostsCommodity(): CommodityInterface
    {
        return $this->tradePost->getLicenceCostGood();
    }
}