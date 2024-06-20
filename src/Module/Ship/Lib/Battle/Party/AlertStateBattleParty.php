<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class AlertStateBattleParty extends AbstractBattleParty implements AlertedBattlePartyInterface
{
    private ShipAlertStateEnum $leaderAlertState;
    private bool $isSingleton = false;

    public function __construct(
        ShipWrapperInterface $leader
    ) {
        parent::__construct($leader);

        $this->leaderAlertState = $leader->get()->getAlertState();
    }

    public function initMembers(): Collection
    {
        $fleet = $this->leader->getFleetWrapper();

        if ($fleet === null) {
            $this->isSingleton = true;
            return $this->createSingleton($this->leader);
        } else {

            // only uncloaked and unwarped ships enter fight
            return $fleet->getShipWrappers()
                ->filter(fn (ShipWrapperInterface $wrapper) => !$wrapper->get()->getCloakState()
                    && !$wrapper->get()->isWarped()
                    && $wrapper->get()->getAlertState()->isAtLeast($this->leaderAlertState));
        }
    }

    public function isSingleton(): bool
    {
        return $this->isSingleton;
    }

    public function getAlertState(): ShipAlertStateEnum
    {
        return $this->leaderAlertState;
    }

    public function getAttackCause(): ShipAttackCauseEnum
    {
        return $this->leaderAlertState->getAttackCause();
    }

    public function getAlertDescription(): string
    {
        return $this->leaderAlertState === ShipAlertStateEnum::ALERT_RED
            ? '[b][color=red]Alarm-Rot[/color][/b]'
            : '[b][color=yellow]Alarm-Gelb[/color][/b]';
    }
}
