<?php

namespace Stu\Lib\Pirate;

enum PirateReactionTriggerEnum: int
{
    case ON_ATTACK = 1;
    case ON_SCAN = 2;
    case ON_INTERCEPTION_BEFORE = 3;
    case ON_INTERCEPTION_AFTER = 4;
    case ON_SUPPORT_CALL = 5;
    case ON_RAGE = 6;
    case ON_TRACTOR = 7;
    case ON_BEAM = 8;

    public function getWrath(): int
    {
        return match ($this) {
            self::ON_ATTACK => 30,
            self::ON_TRACTOR => 25,
            self::ON_BEAM => 15,
            self::ON_INTERCEPTION_BEFORE => 10,
            self::ON_INTERCEPTION_AFTER => 10,
            self::ON_SCAN => 10,
            self::ON_SUPPORT_CALL => 0,
            self::ON_RAGE => 0,
        };
    }

    public function triggerAlternativeReaction(): bool
    {
        return match ($this) {
            self::ON_TRACTOR => false,
            self::ON_ATTACK => true,
            self::ON_INTERCEPTION_BEFORE => false,
            self::ON_INTERCEPTION_AFTER => true,
            self::ON_BEAM => true,
            self::ON_SCAN => true,
            self::ON_SUPPORT_CALL => true,
            self::ON_RAGE => true
        };
    }
}
