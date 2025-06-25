<?php

namespace Stu\Module\Spacecraft\Lib;

use Override;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\TholianWebInterface;

/**
 * @extends SpacecraftWrapper<TholianWebInterface>
 */
class TholianWebWrapper extends SpacecraftWrapper
{
    #[Override]
    public function get(): TholianWebInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        return null;
    }
}
