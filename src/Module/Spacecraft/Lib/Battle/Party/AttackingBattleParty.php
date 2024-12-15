<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class AttackingBattleParty extends AbstractBattleParty
{
    public function __construct(
        private SpacecraftWrapperInterface|FleetWrapperInterface $wrapper
    ) {
        $leader = $wrapper instanceof SpacecraftWrapperInterface ? $wrapper : $wrapper->getLeadWrapper();

        parent::__construct($leader);
    }

    #[Override]
    public function initMembers(): Collection
    {
        if ($this->wrapper instanceof FleetWrapperInterface) {
            return $this->wrapper->getShipWrappers();
        }

        $ship = $this->wrapper->get();
        $fleetWrapper = $this->leader->getFleetWrapper();

        if (
            $ship instanceof ShipInterface
            && $ship->isFleetLeader() && $fleetWrapper !== null
        ) {
            return $fleetWrapper->getShipWrappers();
        } else {
            return $this->createSingleton($this->wrapper);
        }
    }
}
