<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftShieldsTrait
{
    use SpacecraftTrait;
    use HasSpacecraftSystemTrait;

    /**
     * proportional to shield system status
     */
    public function getMaxShield(bool $isTheoretical = false): int
    {
        if ($isTheoretical || !$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
            return $this->max_schilde;
        }

        return (int) (ceil($this->max_schilde
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus() / 100));
    }
}
