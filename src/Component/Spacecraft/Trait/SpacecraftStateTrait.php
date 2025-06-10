<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftStateTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemExistenceTrait;

    public function isWarped(): bool
    {
        return $this->getThis()->getWarpDriveState();
    }

    public function getHealthPercentage(): float
    {
        $self = $this->getThis();
        $condition = $self->getCondition();

        return ($condition->getHull() + $condition->getShield())
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

    public function displayNbsActions(): bool
    {
        return !$this->getThis()->isCloaked()
            && !$this->getThis()->isWarped();
    }
}
