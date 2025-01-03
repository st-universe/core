<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class ColonyDefendingBattleParty extends AbstractBattleParty implements AlertedBattlePartyInterface
{
    public function __construct(
        ShipWrapperInterface $leader
    ) {
        parent::__construct($leader);
    }

    #[Override]
    public function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        if ($fleetWrapper === null) {
            return $this->createSingleton($this->leader);
        } else {

            // only uncloaked ships enter fight
            return $fleetWrapper->getShipWrappers()
                ->filter(fn(SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isCloaked());
        }
    }

    #[Override]
    public function getAttackCause(): SpacecraftAttackCauseEnum
    {
        return SpacecraftAttackCauseEnum::COLONY_DEFENSE;
    }

    #[Override]
    public function getAlertDescription(): string
    {
        return '[b][color=orange]Kolonie-Verteidigung[/color][/b]';
    }
}
