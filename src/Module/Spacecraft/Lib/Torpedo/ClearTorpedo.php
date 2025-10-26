<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;

final class ClearTorpedo implements ClearTorpedoInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private TorpedoStorageRepositoryInterface $torpedoStorageRepository,
        private StorageRepositoryInterface $storageRepository
    ) {}

    #[\Override]
    public function clearTorpedoStorage(SpacecraftWrapperInterface $wrapper): void
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
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TORPEDO, true);
        }
    }
}
