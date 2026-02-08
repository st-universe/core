<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\TholianWeb;

/**
 * @extends SpacecraftWrapper<TholianWeb>
 */
class TholianWebWrapper extends SpacecraftWrapper
{
    #[\Override]
    public function get(): TholianWeb
    {
        return $this->spacecraft;
    }

    #[\Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        return null;
    }
}
