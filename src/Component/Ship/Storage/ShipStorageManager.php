<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Storage;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;

final class ShipStorageManager implements ShipStorageManagerInterface
{
    private ShipStorageRepositoryInterface $shipStorageRepository;

    public function __construct(
        ShipStorageRepositoryInterface $shipStorageRepository
    ) {
        $this->shipStorageRepository = $shipStorageRepository;
    }

    public function lowerStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $storage = $ship->getStorage();

        $storageItem = $storage[$commodity->getId()] ?? null;
        if ($storageItem === null) {
            throw new Exception\CommodityMissingException();
        }

        $storedAmount = $storageItem->getAmount();

        if ($storedAmount < $amount) {
            throw new Exception\QuantityTooSmallException();
        }

        if ($storedAmount === $amount) {
            $storage->removeElement($storageItem);

            $this->shipStorageRepository->delete($storageItem);

            return;
        }
        $storageItem->setAmount($storedAmount - $amount);

        $this->shipStorageRepository->save($storageItem);
    }

    public function upperStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $commodityId = $commodity->getId();
        $storage = $ship->getStorage();

        $storageItem = $storage[$commodityId] ?? null;

        if ($storageItem === null) {
            $storageItem = $this->shipStorageRepository->prototype()
                ->setShip($ship)
                ->setCommodity($commodity);

            $storage->set($commodityId, $storageItem);
        }
        $storageItem->setAmount($storageItem->getAmount() + $amount);

        $this->shipStorageRepository->save($storageItem);
    }
}
