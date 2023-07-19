<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class DealAccountTal implements DealAccountTalInterface
{
    private StorageRepositoryInterface $storageRepository;

    private TradePostInterface $tradePost;

    private int $userId;

    /** @var array<StorageInterface> */
    private ?array $storage = null;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        TradePostInterface $tradePost,
        int $userId
    ) {
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

    private function getStorage(): array
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
            fn (int $value, StorageInterface $storage): int => $value + $storage->getAmount(),
            0
        );
    }

    public function isOverStorage(): bool
    {
        return $this->getStorageSum() > $this->tradePost->getStorage();
    }

    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }
}
