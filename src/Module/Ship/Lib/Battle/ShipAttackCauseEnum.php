<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;

enum ShipAttackCauseEnum
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

    public function getDestructionCause(): ShipDestructionCauseEnum
    {
        return match ($this) {
            self::SHIP_FIGHT => ShipDestructionCauseEnum::SHIP_FIGHT,
            self::ALERT_YELLOW => ShipDestructionCauseEnum::ALERT_YELLOW,
            self::ALERT_RED => ShipDestructionCauseEnum::ALERT_RED,
            self::COLONY_DEFENSE => ShipDestructionCauseEnum::SHIP_FIGHT,
            self::ACTIVATE_TRACTOR => ShipDestructionCauseEnum::SHIP_FIGHT,
            self::BOARD_SHIP => ShipDestructionCauseEnum::SHIP_FIGHT,
            self::THOLIAN_WEB_REFLECTION => ShipDestructionCauseEnum::SHIP_FIGHT
        };
    }
}
