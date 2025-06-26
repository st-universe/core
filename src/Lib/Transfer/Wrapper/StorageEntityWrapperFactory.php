<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use RuntimeException;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Repository\UserRepositoryInterface;

class StorageEntityWrapperFactory implements StorageEntityWrapperFactoryInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly CommodityTransferInterface $commodityTransfer,
        private readonly StorageManagerInterface $storageManager,
        private readonly TroopTransferUtilityInterface $troopTransferUtility,
        private readonly ShipTorpedoManagerInterface $shipTorpedoManager,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly SpacecraftStorageCommodityLogic $spacecraftStorageCommodityLogic,
        private readonly SpacecraftStorageCrewLogic $spacecraftStorageCrewLogic,
        private readonly SpacecraftStorageTorpedoLogic $spacecraftStorageTorpedoLogic
    ) {}

    public function wrapStorageEntity(EntityWithStorageInterface $entity): StorageEntityWrapperInterface
    {
        if ($entity instanceof Colony) {
            return new ColonyStorageEntityWrapper(
                $this->colonyLibFactory,
                $this->commodityTransfer,
                $this->storageManager,
                $this->troopTransferUtility,
                $entity
            );
        }
        if ($entity instanceof Spacecraft) {
            return new SpacecraftStorageEntityWrapper(
                $this->shipTorpedoManager,
                $this->spacecraftStorageCommodityLogic,
                $this->spacecraftStorageCrewLogic,
                $this->spacecraftStorageTorpedoLogic,
                $this->spacecraftWrapperFactory->wrapSpacecraft($entity)
            );
        }
        if ($entity instanceof Trumfield) {
            return new TrumfieldStorageEntityWrapper($this->userRepository, $entity);
        }

        throw new RuntimeException('unknown entity class');
    }
}
