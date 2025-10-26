<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use InvalidArgumentException;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ShipTorpedoManager implements ShipTorpedoManagerInterface
{
    public function __construct(
        private ClearTorpedoInterface $clearTorpedo,
        private TorpedoStorageRepositoryInterface $torpedoStorageRepository,
        private StorageRepositoryInterface $storageRepository
    ) {}

    #[\Override]
    public function changeTorpedo(SpacecraftWrapperInterface $wrapper, int $changeAmount, ?TorpedoType $type = null): void
    {
        $spacecraft = $wrapper->get();

        $torpedoStorage = $spacecraft->getTorpedoStorage();
        if ($torpedoStorage === null) {
            $this->setupNewTorpedo($spacecraft, $changeAmount, $type);
        } elseif ($torpedoStorage->getStorage()->getAmount() + $changeAmount === 0) {
            $this->clearTorpedo->clearTorpedoStorage($wrapper);
        } else {
            $storage = $torpedoStorage->getStorage();
            $storage->setAmount($storage->getAmount() + $changeAmount);
            $this->storageRepository->save($storage);
        }
    }

    private function setupNewTorpedo(Spacecraft $spacecraft, int $changeAmount, ?TorpedoType $type = null): void
    {
        if ($type === null) {
            throw new InvalidArgumentException('can not set torpedo type without type specified');
        }

        $this->createTorpedoStorage($spacecraft, $changeAmount, $type);
    }

    private function createTorpedoStorage(Spacecraft $spacecraft, int $amount, TorpedoType $type): void
    {
        $torpedoStorage = $this->torpedoStorageRepository->prototype();
        $torpedoStorage->setSpacecraft($spacecraft);
        $torpedoStorage->setTorpedo($type);
        $this->torpedoStorageRepository->save($torpedoStorage);

        $storage = $this->storageRepository->prototype();
        $storage->setUser($spacecraft->getUser());
        $storage->setCommodity($type->getCommodity());
        $storage->setAmount($amount);
        $storage->setTorpedoStorage($torpedoStorage);
        $this->storageRepository->save($storage);

        $torpedoStorage->setStorage($storage);

        $spacecraft->setTorpedoStorage($torpedoStorage);
    }
}
