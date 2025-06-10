<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftSystemExistenceTrait
{
    use SpacecraftTrait;

    public function hasSpacecraftSystem(SpacecraftSystemTypeEnum $type): bool
    {
        return $this->getThis()->getSystems()->containsKey($type->value);
    }

    public function hasComputer(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER);
    }

    public function hasPhaser(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER);
    }

    public function hasTorpedo(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO);
    }

    public function hasCloak(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK);
    }

    public function hasShuttleRamp(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHUTTLE_RAMP);
    }

    public function hasWarpdrive(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE);
    }

    public function hasReactor(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE) ||
            $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::FUSION_REACTOR) ||
            $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SINGULARITY_REACTOR);
    }

    public function hasNbs(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::NBS);
    }

    public function hasLss(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LSS);
    }

    public function hasUplink(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK);
    }
}
