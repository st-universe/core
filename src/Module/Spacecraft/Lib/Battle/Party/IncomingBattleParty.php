<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class IncomingBattleParty extends AbstractBattleParty
{
    public function __construct(
        SpacecraftWrapperInterface $leader
    ) {
        parent::__construct($leader);
    }

    #[Override]
    public function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        $result = $fleetWrapper === null ? $this->createSingleton($this->leader) : $fleetWrapper->getShipWrappers();

        // filter warped and cloaked
        return $result
            ->filter(fn(SpacecraftWrapperInterface $wrapper): bool =>
            !$wrapper->get()->getCloakState() && !$wrapper->get()->isWarped());
    }
}
