<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\StationInterface;

trait SpacecrafCharacteristicsTrait
{
    use SpacecraftTrait;

    public function isStation(): bool
    {
        return $this->getThis() instanceof StationInterface;
    }

    public function isShuttle(): bool
    {
        return $this->getThis()->getRump()->getCategoryId() === SpacecraftRumpCategoryEnum::SHIP_CATEGORY_SHUTTLE;
    }

    public function isConstruction(): bool
    {
        return $this->getThis()->getRump()->getCategoryId() === SpacecraftRumpCategoryEnum::SHIP_CATEGORY_CONSTRUCTION;
    }

    public function hasEscapePods(): bool
    {
        return $this->getThis()->getRump()->isEscapePods() && $this->getCrewCount() > 0;
    }
}
