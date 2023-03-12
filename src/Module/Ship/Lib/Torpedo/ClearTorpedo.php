<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Torpedo;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ClearTorpedo implements ClearTorpedoInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        TorpedoStorageRepositoryInterface $torpedoStorageRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->torpedoStorageRepository = $torpedoStorageRepository;
        $this->storageRepository = $storageRepository;
    }

    public function clearTorpedoStorage(ShipWrapperInterface $wrapper): void
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
