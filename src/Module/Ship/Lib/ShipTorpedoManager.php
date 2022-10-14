<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ShipTorpedoManager implements ShipTorpedoManagerInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    private StorageRepositoryInterface $storageRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        TorpedoStorageRepositoryInterface $torpedoStorageRepository,
        StorageRepositoryInterface $storageRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->torpedoStorageRepository = $torpedoStorageRepository;
        $this->storageRepository = $storageRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function changeTorpedo(ShipInterface $ship, int $changeAmount, TorpedoTypeInterface $type = null)
    {
        // OLD
        $ship->setTorpedoCount($ship->getTorpedoCount() + $changeAmount);
        if ($type !== null) {
            $ship->setTorpedo($type);
        }

        if ($ship->getTorpedoCount() == 0) {
            $ship->setTorpedo(null);

            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_TORPEDO, true);
        }

        $this->shipRepository->save($ship);

        if ($ship->getUser()->getId() === 126) {
            $this->loggerUtil->init('torp', LoggerEnum::LEVEL_ERROR);
        }

        // NEW
        if ($ship->getTorpedoStorage() === null && $type !== null) {
            $this->createTorpedoStorage($ship, $changeAmount, $type);
        } else if ($changeAmount === $ship->getTorpedoStorage()->getStorage()->getAmount()) {
            $this->loggerUtil->log('clear');
            $this->clearTorpedoStorage($ship);
        } else {
            $storage = $ship->getTorpedoStorage()->getStorage();
            $this->loggerUtil->log(sprintf('change, current: %d, change: %d',  $storage->getAmount(), $changeAmount));
            $storage->setAmount($storage->getAmount() + $changeAmount);
            $this->storageRepository->save($storage);
        }
    }

    private function createTorpedoStorage(ShipInterface $ship, int $amount, TorpedoTypeInterface $type): void
    {
        $torpedoStorage = $this->torpedoStorageRepository->prototype();
        $torpedoStorage->setShip($ship);
        $torpedoStorage->setTorpedo($type);
        $this->torpedoStorageRepository->save($torpedoStorage);

        $storage = $this->storageRepository->prototype();
        $storage->setUserId($ship->getUser()->getId());
        $storage->setCommodity($type->getCommodity());
        $storage->setAmount($amount);
        $storage->setTorpedoStorage($torpedoStorage);
        $this->storageRepository->save($storage);
    }

    private function clearTorpedoStorage(ShipInterface $ship): void
    {
        $torpedoStorage = $ship->getTorpedoStorage();
        $storage = $torpedoStorage->getStorage();

        $this->storageRepository->delete($storage);
        $this->torpedoStorageRepository->delete($torpedoStorage);

        $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_TORPEDO, true);
    }
}
