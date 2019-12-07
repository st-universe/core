<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

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
        $storage = $ship->getStorage()[$commodity->getId()] ?? null;
        if ($storage === null) {
            return;
        }

        if ($storage->getAmount() <= $amount) {
            $ship->getStorage()->removeElement($storage);

            $this->shipStorageRepository->delete($storage);
            return;
        }
        $storage->setAmount($storage->getAmount() - $amount);

        $this->shipStorageRepository->save($storage);
    }

    public function upperStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $commodityId = $commodity->getId();

        $storage = $ship->getStorage()[$commodityId] ?? null;

        if ($storage === null) {
            $storage = $this->shipStorageRepository->prototype()
                ->setShip($ship)
                ->setCommodity($commodity);

            $ship->getStorage()->set($commodityId, $storage);
        }
        $storage->setAmount($storage->getAmount() + $amount);

        $this->shipStorageRepository->save($storage);
    }
}
