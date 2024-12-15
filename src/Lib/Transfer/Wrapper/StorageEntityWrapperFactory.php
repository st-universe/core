<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use RuntimeException;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Station\Dock\DockPrivilegeUtilityInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TrumfieldInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class StorageEntityWrapperFactory implements StorageEntityWrapperFactoryInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private CommodityTransferInterface $commodityTransfer,
        private StorageManagerInterface $storageManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private PirateReactionInterface $pirateReaction,
        private DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private ShipShutdownInterface $shipShutdown,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    public function wrapStorageEntity(EntityWithStorageInterface $entity): StorageEntityWrapperInterface
    {
        if ($entity instanceof ColonyInterface) {
            return new ColonyStorageEntityWrapper(
                $this->colonyLibFactory,
                $this->commodityTransfer,
                $this->storageManager,
                $this->troopTransferUtility,
                $entity
            );
        }
        if ($entity instanceof SpacecraftInterface) {
            return new SpacecraftStorageEntityWrapper(
                $this->shipTorpedoManager,
                $this->pirateReaction,
                $this->commodityTransfer,
                $this->troopTransferUtility,
                $this->dockPrivilegeUtility,
                $this->activatorDeactivatorHelper,
                $this->spacecraftSystemManager,
                $this->shipCrewCalculator,
                $this->shipShutdown,
                $this->privateMessageSender,
                $this->spacecraftWrapperFactory->wrapSpacecraft($entity)
            );
        }
        if ($entity instanceof TrumfieldInterface) {
            return new TrumfieldStorageEntityWrapper($this->userRepository, $entity);
        }

        throw new RuntimeException('unknown entity class');
    }
}
