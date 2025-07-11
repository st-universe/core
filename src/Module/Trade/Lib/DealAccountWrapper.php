<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class DealAccountWrapper implements DealAccountWrapperInterface
{
    /** @var array<Storage> */
    private ?array $storage = null;

    public function __construct(
        private StorageRepositoryInterface $storageRepository,
        private TradePost $tradePost,
        private int $userId
    ) {}

    #[Override]
    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    #[Override]
    public function getStation(): Station
    {
        return $this->tradePost->getStation();
    }

    /** @return array<Storage> */
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

    #[Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            fn(int $value, Storage $storage): int => $value + $storage->getAmount(),
            0
        );
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
}
