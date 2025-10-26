<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class EnergyWeaponSystemData extends AbstractSystemData
{
    public int $baseDamage = 0;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::PHASER;
    }

    /**
     * proportional to energy weapon system status
     */
    public function getBaseDamage(): int
    {
        return (int) (ceil($this->baseDamage
            * $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getStatus() / 100));
    }

    public function setBaseDamage(int $baseDamage): EnergyWeaponSystemData
    {
        $this->baseDamage = $baseDamage;
        return $this;
    }
}
