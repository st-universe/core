<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TractorBeamShipSystem;
use Stu\Module\Spacecraft\Lib\Battle\FightLib;
use Stu\Orm\Entity\ShipInterface;

trait SpacecraftInteractionTrait
{
    use SpacecraftTrait;
    use HasSpacecraftSystemTrait;
    use SpacecraftSystemHealthTrait;

    public function isTractorbeamPossible(): bool
    {
        return TractorBeamShipSystem::isTractorBeamPossible($this);
    }

    public function isBoardingPossible(): bool
    {
        return FightLib::isBoardingPossible($this);
    }

    public function isInterceptable(): bool
    {
        //TODO can tractored ships be intercepted?!
        return $this->getThis()->getWarpDriveState();
    }

    public function canIntercept(): bool
    {
        $self = $this->getThis();

        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::WARPDRIVE)
            && !$self->isTractoring()
            && (!$self instanceof ShipInterface || !$self->isTractored());
    }

    public function canMove(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)
            || $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE);
    }
}
