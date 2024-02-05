<?php

namespace Stu\Lib\Pirate;

enum PirateBehaviourEnum: int
{
    case DO_NOTHING = 0;
    case FLY = 1;
    case RUB_COLONY = 2;
    case ATTACK_SHIP = 3;
    case HIDE = 4;

    public function getDescription(): string
    {
        return match ($this) {
            self::DO_NOTHING => _("DO_NOTHING"),
            self::FLY => _("FLY"),
            self::RUB_COLONY => _("RUB_COLONY"),
            self::ATTACK_SHIP => _("ATTACK_SHIP"),
            self::HIDE => _("HIDE"),
        };
    }
}
