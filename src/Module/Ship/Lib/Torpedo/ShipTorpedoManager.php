<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Torpedo;

use InvalidArgumentException;
use Override;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ShipTorpedoManager implements ShipTorpedoManagerInterface
{
    public function __construct(private ClearTorpedoInterface $clearTorpedo, private TorpedoStorageRepositoryInterface $torpedoStorageRepository, private StorageRepositoryInterface $storageRepository)
    {
    }

    #[Override]
    public function changeTorpedo(ShipWrapperInterface $wrapper, int $changeAmount, ?TorpedoTypeInterface $type = null): void
    {
        $ship = $wrapper->get();

        $torpedoStorage = $ship->getTorpedoStorage();
        if ($torpedoStorage === null) {
            $this->setupNewTorpedo($ship, $changeAmount, $type);
        } elseif ($torpedoStorage->getStorage()->getAmount() + $changeAmount === 0) {
            $this->clearTorpedo->clearTorpedoStorage($wrapper);
        } else {
            $storage = $torpedoStorage->getStorage();
            $storage->setAmount($storage->getAmount() + $changeAmount);
            $this->storageRepository->save($storage);
        }
    }

    private function setupNewTorpedo(ShipInterface $ship, int $changeAmount, ?TorpedoTypeInterface $type = null): void
    {
        if ($type === null) {
            throw new InvalidArgumentException('can not set torpedo type without type specified');
        }

        $this->createTorpedoStorage($ship, $changeAmount, $type);
    }

    private function createTorpedoStorage(ShipInterface $ship, int $amount, TorpedoTypeInterface $type): void
    {
        $torpedoStorage = $this->torpedoStorageRepository->prototype();
        $torpedoStorage->setShip($ship);
        $torpedoStorage->setTorpedo($type);
        $this->torpedoStorageRepository->save($torpedoStorage);

        $storage = $this->storageRepository->prototype();
        $storage->setUser($ship->getUser());
        $storage->setCommodity($type->getCommodity());
        $storage->setAmount($amount);
        $storage->setTorpedoStorage($torpedoStorage);
        $this->storageRepository->save($storage);

        $torpedoStorage->setStorage($storage);

        $ship->setTorpedoStorage($torpedoStorage);
    }
}
