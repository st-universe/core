<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Override;
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

    #[Override]
    public function initMembers(): Collection
    {
        $fleet = $this->leader->getFleetWrapper();

        $result = $fleet === null ? $this->createSingleton($this->leader) : $fleet->getShipWrappers();

        // filter warped and cloaked
        return $result
            ->filter(fn (ShipWrapperInterface $wrapper): bool =>
            !$wrapper->get()->getCloakState() && !$wrapper->get()->isWarped());
    }
}
