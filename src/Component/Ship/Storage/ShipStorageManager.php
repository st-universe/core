<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Storage;

use Override;
use Stu\Component\Ship\Storage\Exception\CommodityMissingException;
use Stu\Component\Ship\Storage\Exception\QuantityTooSmallException;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShipStorageManager implements ShipStorageManagerInterface
{
    public function __construct(private StorageRepositoryInterface $storageRepository)
    {
    }

    #[Override]
    public function lowerStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $storage = $ship->getStorage();

        $storageItem = $storage[$commodity->getId()] ?? null;
        if ($storageItem === null) {
            throw new CommodityMissingException();
        }

        $storedAmount = $storageItem->getAmount();

        if ($storedAmount < $amount) {
            throw new QuantityTooSmallException();
        }

        if ($storedAmount === $amount) {
            $storage->removeElement($storageItem);

            $this->storageRepository->delete($storageItem);

            return;
        }
        $storageItem->setAmount($storedAmount - $amount);

        $this->storageRepository->save($storageItem);
    }

    #[Override]
    public function upperStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $commodityId = $commodity->getId();
        $storage = $ship->getStorage();

        $storageItem = $storage[$commodityId] ?? null;
        if ($storageItem === null) {
            $storageItem = $this->storageRepository->prototype()
                ->setUser($ship->getUser())
                ->setShip($ship)
                ->setCommodity($commodity);
            $storage->set($commodityId, $storageItem);
        }

        $storageItem->setAmount($storageItem->getAmount() + $amount);

        $this->storageRepository->save($storageItem);
    }
}
