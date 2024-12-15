<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class ProjectileLauncherSystemData extends AbstractSystemData
{
    public int $shieldPenetration = 0;

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SYSTEM_TORPEDO;
    }

    public function getShieldPenetration(): int
    {
        return $this->shieldPenetration;
    }

    public function setShieldPenetration(int $shieldPenetration): ProjectileLauncherSystemData
    {
        $this->shieldPenetration = $shieldPenetration;
        return $this;
    }
}
