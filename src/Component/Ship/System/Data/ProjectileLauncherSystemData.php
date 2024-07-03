<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

class ProjectileLauncherSystemData extends AbstractSystemData
{
    public int $shieldPenetration = 0;

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TORPEDO;
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
