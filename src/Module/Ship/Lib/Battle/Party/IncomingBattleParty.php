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
            $result = $this->createSingleton($this->leader);
        } else {
            $result = $fleet->getShipWrappers();
        }

        // filter warped and cloaked
        return $result
            ->filter(fn (ShipWrapperInterface $wrapper) =>
            !$wrapper->get()->getCloakState() && !$wrapper->get()->isWarped());
    }
}
