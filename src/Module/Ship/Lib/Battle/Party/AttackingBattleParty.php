<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class AttackingBattleParty extends AbstractBattleParty
{
    public function __construct(
        private ShipWrapperInterface|FleetWrapperInterface $wrapper
    ) {
        $leader = $wrapper instanceof ShipWrapperInterface ? $wrapper : $wrapper->getLeadWrapper();

        parent::__construct($leader);
    }

    #[Override]
    public function initMembers(): Collection
    {
        if ($this->wrapper instanceof FleetWrapperInterface) {
            return $this->wrapper->getShipWrappers();
        }

        $ship = $this->wrapper->get();
        $fleetWrapper = $this->wrapper->getFleetWrapper();

        if ($ship->isFleetLeader() && $fleetWrapper !== null) {
            return $fleetWrapper->getShipWrappers();
        } else {
            return $this->createSingleton($this->wrapper);
        }
    }
}
