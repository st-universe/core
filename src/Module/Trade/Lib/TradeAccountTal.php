<?php

// @todo activate strict typing
declare(strict_types=0);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\TradeStorageInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TradeAccountTal implements TradeAccountTalInterface
{
    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeTransferRepositoryInterface $tradeTransferRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private TradePostInterface $tradePost;

    private int $userId;

    private $storage;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransferRepositoryInterface $tradeTransferRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        TradePostInterface $tradePost,
        int $userId
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransferRepository = $tradeTransferRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->tradePost = $tradePost;
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    public function getShip(): ShipInterface
    {
        return $this->tradePost->getShip();
    }

    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    public function getTradePostbyUser(): bool
    {
        if ($this->tradePost->getUserId() === $this->userId)
            return true;
    }

    public function getStorage(): array
    {
        if ($this->storage === null) {
            $this->storage = $this->tradeStorageRepository->getByTradePostAndUser(
                $this->tradePost->getId(),
                $this->userId
            );
        }
        return $this->storage;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            function (int $value, TradeStorageInterface $storage): int {
                return $value + $storage->getAmount();
            },
            0
        );
    }

    public function getOfferStorage(): array
    {
        return $this->tradeOfferRepository->getGroupedSumByTradePostAndUser(
            $this->tradePost->getId(),
            $this->userId
        );
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
        return $this->getStorageSum() > $this->tradePost->getStorage();
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
        return max(0, $this->tradePost->getStorage() - $this->getStorageSum());
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