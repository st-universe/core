<?php

declare(strict_types=1);

namespace Stu\Component\Player;

final class UserRpgEnum
{
    //RPG behavior
    public const RPG_BEHAVIOR_NOT_SET = 0;
    public const RPG_BEHAVIOR_ACTIVE = 1;
    public const RPG_BEHAVIOR_OPEN = 2;
    public const RPG_BEHAVIOR_NONE = 3;

    public const RPG_BEHAVIOR = [
        self::RPG_BEHAVIOR_ACTIVE => ['rpg' => self::RPG_BEHAVIOR_ACTIVE, 'title' => 'Aktiver Rollenspieler'],
        self::RPG_BEHAVIOR_OPEN => ['rpg' => self::RPG_BEHAVIOR_OPEN, 'title' => 'Gelegentlicher Rollenspieler'],
        self::RPG_BEHAVIOR_NONE => ['rpg' => self::RPG_BEHAVIOR_NONE, 'title' => 'Kein Rollenspieler'],
    ];
}
