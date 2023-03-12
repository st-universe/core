<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use InvalidArgumentException;

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

    public static function getRpgBehaviorText(int $behaviorType): string
    {
        switch ($behaviorType) {
            case self::RPG_BEHAVIOR_NOT_SET:
                return _("Der Spieler hat seine Rollenspieleinstellung nicht gesetzt");
            case self::RPG_BEHAVIOR_ACTIVE:
                return _("Der Spieler betreibt gerne Rollenspiel");
            case self::RPG_BEHAVIOR_OPEN:
                return _("Der Spieler betreibt gelegentlich Rollenspiel");
            case self::RPG_BEHAVIOR_NONE:
                return _("Der Spieler betreibt ungern Rollenspiel");
            default:
                throw new InvalidArgumentException('unknown rpg behavior type');
        }
    }
}
