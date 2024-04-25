<?php

namespace Stu\Lib\Pirate;

enum PirateBehaviourEnum: int
{
    case DO_NOTHING = 0;
    case FLY = 1;
    case RUB_COLONY = 2;
    case ATTACK_SHIP = 3;
    case HIDE = 4;
    case RAGE = 5;
    case GO_ALERT_RED = 6;
    case CALL_FOR_SUPPORT = 7;

    public function getDescription(): string
    {
        return match ($this) {
            self::DO_NOTHING => _("DO_NOTHING"),
            self::FLY => _("FLY"),
            self::RUB_COLONY => _("RUB_COLONY"),
            self::ATTACK_SHIP => _("ATTACK_SHIP"),
            self::HIDE => _("HIDE"),
            self::RAGE => _("RAGE"),
            self::GO_ALERT_RED => _("GO_ALERT_RED"),
            self::CALL_FOR_SUPPORT => _("CALL_FOR_SUPPORT"),
        };
    }
}
