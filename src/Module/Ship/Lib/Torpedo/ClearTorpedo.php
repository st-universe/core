<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Torpedo;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ClearTorpedo implements ClearTorpedoInterface
{
    public function __construct(private ShipSystemManagerInterface $shipSystemManager, private TorpedoStorageRepositoryInterface $torpedoStorageRepository, private StorageRepositoryInterface $storageRepository)
    {
    }

    #[Override]
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
