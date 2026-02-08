<?php

namespace Stu\Component\Spacecraft\Trait;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Storage;

trait SpacecraftStorageTrait
{
    use SpacecraftTrait;

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getThis()->getStorage()->getValues(),
            fn (int $sum, Storage $storage): int => $sum + $storage->getAmount(),
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

    /** @return Collection<int, Commodity> */
    public function getStoredShuttles(): Collection
    {
        return $this->getThis()->getStorage()
            ->map(fn (Storage $storage): Commodity => $storage->getCommodity())
            ->filter(fn (Commodity $commodity): bool => $commodity->isShuttle());
    }

    public function hasStoredBuoy(): bool
    {
        return $this->getThis()->getStorage()
            ->exists(fn (int $key, Storage $storage): bool => $storage->getCommodity()->isBouy());
    }
}
