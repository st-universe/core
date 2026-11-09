<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class BorgSphereTorpedoSupply
{
    public function __construct(
        private SpacecraftWrapperFactoryInterface $wrappers,
        private ShipTorpedoManagerInterface $torpedoes,
        private TorpedoTypeRepositoryInterface $types,
        private TorpedoStorageRepositoryInterface $storages,
        private StuRandom $random
    ) {}

    public function refill(Ship $ship): void
    {
        if (!$ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO) || $ship->getMaxTorpedos() <= 0) {
            return;
        }
        $type = $this->selectedType($ship);
        $missing = max(0, $ship->getMaxTorpedos() - $ship->getTotalTorpedoCount());
        if ($missing > 0) {
            $type ??= $this->defaultType($ship);
            if ($type === null) {
                return;
            }
            $this->torpedoes->changeTorpedo($this->wrappers->wrapShip($ship), $missing, $type);
        }
        $this->readyLauncher($ship);
    }

    private function selectedType(Ship $ship): ?TorpedoType
    {
        $type = $ship->getTorpedo();
        if ($type !== null) {
            return $type;
        }
        $storage = array_values($ship->getFireableTorpedoStorages())[0] ?? null;
        $storage ??= array_find($ship->getTorpedoStorages()->toArray(), static fn (TorpedoStorage $candidate): bool => $candidate->getTorpedo()->getLevel() === $ship->getRump()->getTorpedoLevel());
        if ($storage === null) {
            return null;
        }
        $storage->setActive(true);
        $this->storages->save($storage);
        return $storage->getTorpedo();
    }

    private function defaultType(Ship $ship): ?TorpedoType
    {
        $types = $this->types->getByLevel($ship->getRump()->getTorpedoLevel());
        return $types === [] ? null : $types[$this->random->array_rand($types)];
    }

    private function readyLauncher(Ship $ship): void
    {
        if (!$ship->getCondition()->isDisabled() && !$ship->getCondition()->isDestroyed()
            && $ship->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO) && $ship->getTorpedoCount() > 0) {
            $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        }
    }
}
