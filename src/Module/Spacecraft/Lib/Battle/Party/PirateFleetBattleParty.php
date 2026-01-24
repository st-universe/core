<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PirateFleetBattleParty extends AbstractBattleParty
{
    public function __construct(
        private readonly FleetWrapperInterface $fleetWrapper,
        StuRandom $stuRandom,
    ) {
        parent::__construct($fleetWrapper->getLeadWrapper(), $stuRandom);
    }

    #[\Override]
    public function getLeader(): SpacecraftWrapperInterface
    {
        if ($this->isDefeated()) {
            throw new RuntimeException('Cannot get leader of defeated party');
        }
        return $this->fleetWrapper->getLeadWrapper();
    }

    #[\Override]
    public function initMembers(): Collection
    {
        return $this->fleetWrapper->getShipWrappers();
    }

    public function getFleetId(): int
    {
        return $this->fleetWrapper->get()->getId();
    }

    public function getFleetName(): string
    {
        return $this->fleetWrapper->get()->getName();
    }
}
