<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftHitChanceTrait
{
    use SpacecraftTrait;
    use HasSpacecraftSystemTrait;

    /**
     * proportional to computer system status
     */
    public function getHitChance(): int
    {
        if (!$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)) {
            return $this->hit_chance;
        }

        return (int) (ceil($this->hit_chance
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)->getStatus() / 100));
    }
}
