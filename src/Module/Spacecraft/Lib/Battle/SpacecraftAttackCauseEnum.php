<?php

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;

enum SpacecraftAttackCauseEnum
{
    case SHIP_FIGHT;
    case ALERT_YELLOW;
    case ALERT_RED;
    case COLONY_DEFENSE;
    case ACTIVATE_TRACTOR;
    case BOARD_SHIP;
    case THOLIAN_WEB_REFLECTION;
    case TARGET_CAPTURED_IN_THOLIAN_WEB;

    public function isOneWay(): bool
    {
        return match ($this) {
            self::ACTIVATE_TRACTOR,
            self::BOARD_SHIP,
            self::THOLIAN_WEB_REFLECTION,
            self::TARGET_CAPTURED_IN_THOLIAN_WEB => true,
            default => false
        };
    }

    public function getDestructionCause(): SpacecraftDestructionCauseEnum
    {
        return match ($this) {
            self::ALERT_YELLOW => SpacecraftDestructionCauseEnum::ALERT_YELLOW,
            self::ALERT_RED => SpacecraftDestructionCauseEnum::ALERT_RED,
            default => SpacecraftDestructionCauseEnum::SHIP_FIGHT
        };
    }
}
