<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class LssSystemData extends AbstractSystemData
{
    public int $sensorRange  = 0;
    public SpacecraftLssModeEnum $mode = SpacecraftLssModeEnum::NORMAL;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::LSS;
    }

    public function getTheoreticalMaxSensorRange(): int
    {
        return $this->sensorRange;
    }

    public function getSensorRange(): int
    {
        return (int) (ceil($this->sensorRange
            * $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::LSS)->getStatus() / 100));
    }

    public function setSensorRange(int $sensorRange): LssSystemData
    {
        $this->sensorRange = $sensorRange;

        return $this;
    }

    public function getMode(): SpacecraftLssModeEnum
    {
        return $this->mode;
    }

    public function setMode(SpacecraftLssModeEnum $lssMode): LssSystemData
    {
        $this->mode = $lssMode;
        return $this;
    }
}
