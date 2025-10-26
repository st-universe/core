<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class IncomingBattleParty extends AbstractBattleParty
{
    #[\Override]
    public function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        $result = $fleetWrapper === null ? $this->createSingleton($this->leader) : $fleetWrapper->getShipWrappers();

        // filter warped and cloaked
        return $result
            ->filter(fn(SpacecraftWrapperInterface $wrapper): bool =>
            !$wrapper->get()->isCloaked() && !$wrapper->get()->isWarped());
    }
}
