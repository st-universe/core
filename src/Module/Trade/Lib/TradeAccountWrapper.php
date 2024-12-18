<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TradeAccountWrapper implements TradeAccountWrapperInterface
{
    /**
     * @var StorageInterface[]|null
     */
    private ?array $storage = null;

    public function __construct(private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeTransferRepositoryInterface $tradeTransferRepository, private TradeOfferRepositoryInterface $tradeOfferRepository, private StorageRepositoryInterface $storageRepository, private TradePostInterface $tradePost, private int $userId) {}

    #[Override]
    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    #[Override]
    public function getStation(): StationInterface
    {
        return $this->tradePost->getStation();
    }

    #[Override]
    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    #[Override]
    public function getTradePostName(): string
    {
        return $this->tradePost->getName();
    }

    #[Override]
    public function getTradePostbyUser(): bool
    {
        return $this->tradePost->getUserId() === $this->userId;
    }

    #[Override]
    public function getTradePostIsNPC(): bool
    {
        return $this->tradePost->getUser()->isNpc();
    }

    #[Override]
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

    #[Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            fn(int $value, StorageInterface $storage): int => $value + $storage->getAmount(),
            0
        );
    }

    #[Override]
    public function getOfferStorage(): array
    {
        return $this->tradeOfferRepository->getGroupedSumByTradePostAndUser(
            $this->tradePost->getId(),
            $this->userId
        );
    }

    #[Override]
    public function getTradeNetwork(): int
    {
        return $this->tradePost->getTradeNetwork();
    }

    #[Override]
    public function getFreeTransferCapacity(): int
    {
        return $this->tradePost->getTransferCapacity() - $this->tradeTransferRepository->getSumByPostAndUser(
            $this->tradePost->getId(),
            $this->userId
        );
    }

    #[Override]
    public function getTransferCapacity(): int
    {
        return $this->tradePost->getTransferCapacity();
    }

    #[Override]
    public function isOverStorage(): bool
    {
        return $this->getStorageSum() > $this->tradePost->getStorage();
    }

    #[Override]
    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }

    #[Override]
    public function getLicenseCount(): int
    {
        return $this->tradeLicenseRepository->getAmountByTradePost($this->tradePost->getId());
    }

    #[Override]
    public function getFreeStorage(): int
    {
        return max(0, $this->tradePost->getStorage() - $this->getStorageSum());
    }
}