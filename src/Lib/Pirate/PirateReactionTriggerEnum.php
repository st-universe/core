<?php

namespace Stu\Lib\Pirate;

enum PirateReactionTriggerEnum: int
{
    case ON_ATTACK = 1;
    case ON_SCAN = 2;
    case ON_INTERCEPTION = 3;
    case ON_SUPPORT_CALL = 4;
    case ON_RAGE = 5;

    public function getWrath(): int
    {
        return match ($this) {
            self::ON_ATTACK => 10,
            self::ON_SCAN => 1,
            self::ON_INTERCEPTION => 2,
            self::ON_SUPPORT_CALL => 0,
            self::ON_RAGE => 0,
        };
    }
}
