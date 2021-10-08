<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

use Stu\Component\Building\BuildingEnum;
use Stu\Orm\Entity\ColonyInterface;

final class ColonyShieldsManager
{

    public static function updateActualShields(ColonyInterface $colony): void
    {
        $shieldState = false;
        $shields = 0;

        foreach ($colony->getPlanetFields() as $field) {
            $building = $field->getBuilding();

            if ($building === null || !$field->isActive()) {
                continue;
            }

            if ($building->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR)) {
                $shields += BuildingEnum::SHIELD_GENERATOR_CAPACITY;
                $shieldState = true;
            }

            if ($building->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY)) {
                $shields += BuildingEnum::SHIELD_BATTERY_CAPACITY;
            }
        }

        if ($shieldState) {
            $colony->setShields(min($colony->getShields(), $shields));
        }
    }
}
