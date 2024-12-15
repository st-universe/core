<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

enum ShipInteractionEnum
{
    case ACTIVATE_TRACTOR;
    case BOARD_SHIP;

    public function getInteractionText(SpacecraftWrapperInterface $wrapper, SpacecraftWrapperInterface $targetWrapper): string
    {
        $shipName = $wrapper->get()->getName();
        $targetName = $targetWrapper->get()->getName();
        $sector = $wrapper->get()->getSectorString();

        return match ($this) {
            self::ACTIVATE_TRACTOR => sprintf(
                "Die %s versucht die %s in Sektor %s mit dem Traktorstrahl zu erfassen.",
                $shipName,
                $targetName,
                $sector
            ),
            self::BOARD_SHIP => sprintf(
                "Die %s versucht die %s in Sektor %s zu entern.",
                $shipName,
                $targetName,
                $sector
            )
        };
    }

    public function getAttackCause(): SpacecraftAttackCauseEnum
    {
        return match ($this) {
            self::ACTIVATE_TRACTOR => SpacecraftAttackCauseEnum::ACTIVATE_TRACTOR,
            self::BOARD_SHIP => SpacecraftAttackCauseEnum::BOARD_SHIP
        };
    }
}
