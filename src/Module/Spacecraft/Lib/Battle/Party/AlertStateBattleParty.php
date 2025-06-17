<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class AlertStateBattleParty extends AbstractBattleParty implements AlertedBattlePartyInterface
{
    private SpacecraftAlertStateEnum $leaderAlertState;
    private bool $isSingleton = false;

    public function __construct(
        SpacecraftWrapperInterface $leader
    ) {
        parent::__construct($leader);

        $this->leaderAlertState = $leader->get()->getAlertState();
    }

    #[Override]
    public function initMembers(): Collection
    {
        $fleet = $this->leader->getFleetWrapper();

        if ($fleet === null) {
            $this->isSingleton = true;
            return $this->createSingleton($this->leader);
        } else {

            // only uncloaked and unwarped ships enter fight
            return $fleet->getShipWrappers()
                ->filter(fn(SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isCloaked()
                    && !$wrapper->get()->isWarped()
                    && $wrapper->get()->getAlertState()->isAtLeast($this->leaderAlertState));
        }
    }

    public function isSingleton(): bool
    {
        return $this->isSingleton;
    }

    public function getAlertState(): SpacecraftAlertStateEnum
    {
        return $this->leaderAlertState;
    }

    #[Override]
    public function getAttackCause(): SpacecraftAttackCauseEnum
    {
        return match ($this->leaderAlertState) {
            SpacecraftAlertStateEnum::ALERT_GREEN => throw new RuntimeException('this should not happen'),
            SpacecraftAlertStateEnum::ALERT_YELLOW => SpacecraftAttackCauseEnum::ALERT_YELLOW,
            SpacecraftAlertStateEnum::ALERT_RED => SpacecraftAttackCauseEnum::ALERT_RED
        };
    }

    #[Override]
    public function getAlertDescription(): string
    {
        return $this->leaderAlertState === SpacecraftAlertStateEnum::ALERT_RED
            ? '[b][color=red]Alarm-Rot[/color][/b]'
            : '[b][color=yellow]Alarm-Gelb[/color][/b]';
    }
}
