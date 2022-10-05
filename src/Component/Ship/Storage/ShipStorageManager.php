<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Storage;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShipStorageManager implements ShipStorageManagerInterface
{
    private ShipStorageRepositoryInterface $shipStorageRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        ShipStorageRepositoryInterface $shipStorageRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->shipStorageRepository = $shipStorageRepository;
        $this->storageRepository = $storageRepository;
    }

    public function lowerStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $storage = $ship->getStorage();
        $storageNew = $ship->getStorageNew();

        $storageItem = $storage[$commodity->getId()] ?? null;
        if ($storageItem === null) {
            throw new Exception\CommodityMissingException();
        }

        $storageItemNew = $storageNew[$commodity->getId()] ?? null;
        if ($storageItemNew === null) {
            throw new Exception\CommodityMissingException();
        }

        $storedAmount = $storageItem->getAmount();
        $storedAmountNew = $storageItemNew->getAmount();

        if ($storedAmount < $amount) {
            throw new Exception\QuantityTooSmallException();
        }
        if ($storedAmountNew < $amount) {
            throw new Exception\QuantityTooSmallException();
        }

        if ($storedAmount === $amount) {
            $storage->removeElement($storageItem);
            $storageNew->removeElement($storageItemNew);

            $this->shipStorageRepository->delete($storageItem);
            $this->storageRepository->delete($storageItemNew);

            return;
        }
        $storageItem->setAmount($storedAmount - $amount);
        $storageItemNew->setAmount($storedAmountNew - $amount);

        $this->shipStorageRepository->save($storageItem);
        $this->storageRepository->save($storageItemNew);
    }

    public function upperStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $commodityId = $commodity->getId();
        $storage = $ship->getStorage();
        $storageNew = $ship->getStorageNew();

        $storageItem = $storage[$commodityId] ?? null;
        $storageItemNew = $storageNew[$commodityId] ?? null;

        if ($storageItem === null) {
            $storageItem = $this->shipStorageRepository->prototype()
                ->setShip($ship)
                ->setCommodity($commodity);

            $storage->set($commodityId, $storageItem);
        }
        if ($storageItemNew === null) {
            $storageItemNew = $this->storageRepository->prototype()
                ->setShip($ship)
                ->setCommodity($commodity);
            $storageNew->set($commodityId, $storageItemNew);
        }
        $storageItem->setAmount($storageItem->getAmount() + $amount);
        $storageItemNew->setAmount($storageItemNew->getAmount() + $amount);

        $this->shipStorageRepository->save($storageItem);
        $this->storageRepository->save($storageItemNew);
    }
}
