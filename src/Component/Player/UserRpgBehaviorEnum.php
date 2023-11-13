<?php

declare(strict_types=1);

namespace Stu\Component\Player;

enum UserRpgBehaviorEnum: int
{
    case RPG_BEHAVIOR_NOT_SET = 0;
    case RPG_BEHAVIOR_ACTIVE = 1;
    case RPG_BEHAVIOR_OPEN = 2;
    case RPG_BEHAVIOR_NONE = 3;

    public function getDescription(): string
    {
        return match ($this) {
            self::RPG_BEHAVIOR_NOT_SET =>  _("Der Spieler hat seine Rollenspieleinstellung nicht gesetzt"),
            self::RPG_BEHAVIOR_ACTIVE =>  _("Der Spieler betreibt gerne Rollenspiel"),
            self::RPG_BEHAVIOR_OPEN =>  _("Der Spieler betreibt gelegentlich Rollenspiel"),
            self::RPG_BEHAVIOR_NONE =>  _("Der Spieler betreibt ungern Rollenspiel")
        };
    }
}
