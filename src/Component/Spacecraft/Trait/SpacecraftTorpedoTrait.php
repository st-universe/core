<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TorpedoStorageShipSystem;
use Stu\Orm\Entity\TorpedoType;

trait SpacecraftTorpedoTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemHealthTrait;

    public function getTorpedoCount(): int
    {
        $torpedoStorage = $this->getThis()->getTorpedoStorage();
        if ($torpedoStorage === null) {
            return 0;
        }

        return $torpedoStorage->getStorage()->getAmount();
    }

    public function getTorpedo(): ?TorpedoType
    {
        $torpedoStorage = $this->getThis()->getTorpedoStorage();
        if ($torpedoStorage === null) {
            return null;
        }

        return $torpedoStorage->getTorpedo();
    }

    public function getMaxTorpedos(): int
    {
        return $this->getThis()->getRump()->getBaseTorpedoStorage()
            + ($this->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
                ? TorpedoStorageShipSystem::TORPEDO_CAPACITY : 0);
    }
}
