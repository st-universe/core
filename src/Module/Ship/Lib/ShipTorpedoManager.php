<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ShipTorpedoManager implements ShipTorpedoManagerInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    private StorageRepositoryInterface $storageRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        TorpedoStorageRepositoryInterface $torpedoStorageRepository,
        StorageRepositoryInterface $storageRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->torpedoStorageRepository = $torpedoStorageRepository;
        $this->storageRepository = $storageRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function changeTorpedo(ShipWrapperInterface $wrapper, int $changeAmount, TorpedoTypeInterface $type = null)
    {
        $ship = $wrapper->get();

        if ($ship->getTorpedoStorage() === null && $type !== null) {
            $this->createTorpedoStorage($ship, $changeAmount, $type);
        } elseif ($ship->getTorpedoStorage()->getStorage()->getAmount() + $changeAmount === 0) {
            $this->loggerUtil->log('clear');
            $this->clearTorpedoStorage($wrapper);
        } else {
            $storage = $ship->getTorpedoStorage()->getStorage();
            $this->loggerUtil->log(sprintf('change, current: %d, change: %d', $storage->getAmount(), $changeAmount));
            $storage->setAmount($storage->getAmount() + $changeAmount);
            $this->storageRepository->save($storage);
        }
    }

    public function removeTorpedo(ShipWrapperInterface $wrapper)
    {
        $this->clearTorpedoStorage($wrapper);
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
    }

    private function clearTorpedoStorage(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        $torpedoStorage = $ship->getTorpedoStorage();

        if ($torpedoStorage === null) {
            return;
        }

        $storage = $torpedoStorage->getStorage();

        $ship->setTorpedoStorage(null);

        $this->storageRepository->delete($storage);
        $this->torpedoStorageRepository->delete($torpedoStorage);

        if ($ship->getTorpedoState()) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TORPEDO, true);
        }
    }
}
