<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class IncomingBattleParty extends AbstractBattleParty
{
    public function __construct(
        ShipWrapperInterface $leader
    ) {
        parent::__construct($leader);
    }

    public function initMembers(): Collection
    {
        $fleet = $this->leader->getFleetWrapper();
        if ($fleet === null) {

            // if flying ship is cloaked, nothing happens
            if ($this->leader->get()->getCloakState()) {
                return  new ArrayCollection();
            }

            return $this->createSingleton($this->leader);
        }

        // filter warped and cloaked
        return $fleet->getShipWrappers()
            ->filter(fn (ShipWrapperInterface $wrapper) => !$wrapper->get()->getCloakState()
                && !$wrapper->get()->isWarped());
    }
}
