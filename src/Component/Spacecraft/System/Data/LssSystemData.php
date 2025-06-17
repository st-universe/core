<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class LssSystemData extends AbstractSystemData
{
    public int $sensorRange  = 0;

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::LSS;
    }

    public function getSensorRange(): int
    {
        return $this->sensorRange;
    }

    public function setSensorRange(int $sensorRange): LssSystemData
    {
        $this->sensorRange = $sensorRange;

        return $this;
    }
}
