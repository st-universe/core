<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

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
        $fleet = $this->leader->getFleetWrapper();

        if ($fleet === null) {
            return $this->createSingleton($this->leader);
        } else {

            // only uncloaked ships enter fight
            return $fleet->getShipWrappers()
                ->filter(fn (ShipWrapperInterface $wrapper): bool => !$wrapper->get()->getCloakState());
        }
    }

    #[Override]
    public function getAttackCause(): ShipAttackCauseEnum
    {
        return ShipAttackCauseEnum::COLONY_DEFENSE;
    }

    #[Override]
    public function getAlertDescription(): string
    {
        return '[b][color=orange]Kolonie-Verteidigung[/color][/b]';
    }
}
