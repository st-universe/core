<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

enum ShipInteractionEnum
{
    case ACTIVATE_TRACTOR;
    case BOARD_SHIP;

    public function getInteractionText(ShipWrapperInterface $wrapper, ShipWrapperInterface $targetWrapper): string
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

    public function getAttackCause(): ShipAttackCauseEnum
    {
        return match ($this) {
            self::ACTIVATE_TRACTOR => ShipAttackCauseEnum::ACTIVATE_TRACTOR,
            self::BOARD_SHIP => ShipAttackCauseEnum::BOARD_SHIP
        };
    }
}
