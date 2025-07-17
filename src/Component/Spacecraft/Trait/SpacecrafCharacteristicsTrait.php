<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\Station;

trait SpacecrafCharacteristicsTrait
{
    use SpacecraftTrait;

    public function isStation(): bool
    {
        return $this->getThis() instanceof Station;
    }

    public function isShuttle(): bool
    {
        return $this->getThis()->getRump()->getCategoryId() === SpacecraftRumpCategoryEnum::SHUTTLE;
    }

    public function isConstruction(): bool
    {
        return $this->getThis()->getRump()->getCategoryId() === SpacecraftRumpCategoryEnum::CONSTRUCTION;
    }

    public function hasEscapePods(): bool
    {
        return $this->getThis()->getRump()->isEscapePods() && $this->getCrewCount() > 0;
    }
}
