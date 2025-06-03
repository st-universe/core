<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftEvadeChanceTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemExistenceTrait;

    /**
     * proportional to impulsedrive system status
     */
    public function getEvadeChance(): int
    {
        if (!$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)) {
            return $this->evade_chance;
        }

        return (int) (ceil($this->evade_chance
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)->getStatus() / 100));
    }
}
