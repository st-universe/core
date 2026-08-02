<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TorpedoStorage;
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
    public function clearTorpedoStorage(SpacecraftWrapperInterface $wrapper, ?TorpedoStorage $torpedoStorage = null): void
    {
        $ship = $wrapper->get();
        $torpedoStorages = $torpedoStorage === null
            ? $ship->getTorpedoStorages()->toArray()
            : [$torpedoStorage];

        if ($torpedoStorages === []) {
            return;
        }

        $replaceActiveTorpedo = false;
        foreach ($torpedoStorages as $storageToClear) {
            $replaceActiveTorpedo = $replaceActiveTorpedo || $storageToClear->isActive();
            $ship->removeTorpedoStorage($storageToClear);
            $this->storageRepository->delete($storageToClear->getStorage());
            $this->torpedoStorageRepository->delete($storageToClear);
        }

        if ($replaceActiveTorpedo) {
            $replacement = $ship->getFireableTorpedoStorages()[0] ?? null;
            if ($replacement !== null) {
                $replacement->setActive(true);
                $this->torpedoStorageRepository->save($replacement);
            }
        }

        if ($ship->getTorpedoState() && $ship->getTorpedoCount() === 0) {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TORPEDO, true);
        }
    }
}
