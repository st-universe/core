<?php

namespace Stu\Component\Spacecraft\Trait;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StorageInterface;

trait SpacecraftStorageTrait
{
    use SpacecraftTrait;

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getThis()->getStorage()->getValues(),
            fn(int $sum, StorageInterface $storage): int => $sum + $storage->getAmount(),
            0
        );
    }

    public function getMaxStorage(): int
    {
        return $this->getRump()->getStorage();
    }

    public function getBeamableStorage(): Collection
    {
        return CommodityTransfer::excludeNonBeamable($this->storage);
    }

    public function getStoredShuttles(): Collection
    {
        return $this->getThis()->getStorage()
            ->map(fn(StorageInterface $storage): CommodityInterface => $storage->getCommodity())
            ->filter(fn(CommodityInterface $commodity): bool => $commodity->isShuttle());
    }

    public function hasStoredBuoy(): bool
    {
        return $this->getThis()->getStorage()
            ->exists(fn(int $key, StorageInterface $storage): bool => $storage->getCommodity()->isBouy());
    }
}
