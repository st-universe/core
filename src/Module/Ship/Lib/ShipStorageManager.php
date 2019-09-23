<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;

final class ShipStorageManager implements ShipStorageManagerInterface
{
    private $shipStorageRepository;

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

        $ship->clearCache();

        if ($storage->getAmount() <= $amount) {
            $this->shipStorageRepository->delete($storage);
            return;
        }
        $storage->setAmount($storage->getAmount() - $amount);

        $this->shipStorageRepository->save($storage);
    }

    public function upperStorage(ShipInterface $ship, CommodityInterface $commodity, int $amount): void
    {
        $storage = $ship->getStorage()[$commodity->getId()] ?? null;

        if ($storage === null) {
            $storage = $this->shipStorageRepository->prototype()
                ->setShipId((int)$ship->getId())
                ->setCommodity($commodity);
        }
        $storage->setAmount($storage->getAmount() + $amount);

        $this->shipStorageRepository->save($storage);

        $ship->clearCache();
    }
}