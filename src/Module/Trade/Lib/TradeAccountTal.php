<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TradeAccountTal implements TradeAccountTalInterface
{
    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeTransferRepositoryInterface $tradeTransferRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private StorageRepositoryInterface $storageRepository;

    private TradePostInterface $tradePost;

    private int $userId;

    /**
     * @var StorageInterface[]|null
     */
    private ?array $storage = null;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransferRepositoryInterface $tradeTransferRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        StorageRepositoryInterface $storageRepository,
        TradePostInterface $tradePost,
        int $userId
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransferRepository = $tradeTransferRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->storageRepository = $storageRepository;
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

    public function getTradePostName(): string
    {
        return $this->tradePost->getName();
    }

    public function getTradePostbyUser(): bool
    {
        return $this->tradePost->getUserId() === $this->userId;
    }

    public function getTradePostIsNPC(): bool
    {
        return $this->tradePost->getUser()->isNpc();
    }

    public function getStorage(): array
    {
        if ($this->storage === null) {
            $this->storage = $this->storageRepository->getByTradePostAndUser(
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
            fn(int $value, StorageInterface $storage): int => $value + $storage->getAmount(),
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
            $this->tradePost->getId(),
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
        return $this->tradeLicenseRepository->getAmountByTradePost($this->tradePost->getId());
    }

    public function getFreeStorage(): int
    {
        return max(0, $this->tradePost->getStorage() - $this->getStorageSum());
    }
}
