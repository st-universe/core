<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftInterface;

trait SpacecraftStateTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemExistenceTrait;

    public function isWarped(): bool
    {
        return $this->getThis()->getWarpDriveState();
    }

    public function isAlertGreen(): bool
    {
        return $this->getThis()->getAlertState() === SpacecraftAlertStateEnum::ALERT_GREEN;
    }

    public function isUnderRepair(): bool
    {
        return $this->getThis()->getState() === SpacecraftStateEnum::REPAIR_ACTIVE
            || $this->getThis()->getState() === SpacecraftStateEnum::REPAIR_PASSIVE;
    }

    public function getHealthPercentage(): float
    {
        $self = $this->getThis();
        return ($self->getHull() + $self->getShield())
            / ($self->getMaxHull() + $self->getMaxShield(true)) * 100;
    }

    public function isTractoring(): bool
    {
        return $this->getThis()->getTractoredShip() !== null;
    }

    public function isWarpPossible(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE) && $this->getSystem() === null;
    }

    public function setAlertStateGreen(): SpacecraftInterface
    {
        return $this->getThis()->setAlertState(SpacecraftAlertStateEnum::ALERT_GREEN);
    }

    public function displayNbsActions(): bool
    {
        return !$this->getThis()->isCloaked()
            && !$this->getThis()->isWarped();
    }
}
