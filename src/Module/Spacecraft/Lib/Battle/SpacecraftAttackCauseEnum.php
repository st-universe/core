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

    public function isOneWay(): bool
    {
        return match ($this) {
            self::SHIP_FIGHT => false,
            self::ALERT_YELLOW => false,
            self::ALERT_RED => false,
            self::COLONY_DEFENSE => false,
            self::ACTIVATE_TRACTOR => true,
            self::BOARD_SHIP => true,
            self::THOLIAN_WEB_REFLECTION => true,
        };
    }

    public function getDestructionCause(): SpacecraftDestructionCauseEnum
    {
        return match ($this) {
            self::SHIP_FIGHT => SpacecraftDestructionCauseEnum::SHIP_FIGHT,
            self::ALERT_YELLOW => SpacecraftDestructionCauseEnum::ALERT_YELLOW,
            self::ALERT_RED => SpacecraftDestructionCauseEnum::ALERT_RED,
            self::COLONY_DEFENSE => SpacecraftDestructionCauseEnum::SHIP_FIGHT,
            self::ACTIVATE_TRACTOR => SpacecraftDestructionCauseEnum::SHIP_FIGHT,
            self::BOARD_SHIP => SpacecraftDestructionCauseEnum::SHIP_FIGHT,
            self::THOLIAN_WEB_REFLECTION => SpacecraftDestructionCauseEnum::SHIP_FIGHT
        };
    }
}
