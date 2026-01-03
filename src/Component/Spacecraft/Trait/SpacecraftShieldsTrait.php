<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftShieldsTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemExistenceTrait;

    /**
     * proportional to shield system status
     */
    public function getMaxShield(bool $isTheoretical = false): int
    {
        if ($isTheoretical || !$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
            return $this->maxShield;
        }

        return (int) (ceil($this->maxShield
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus() / 100));
    }
}
