<?php

declare(strict_types=1);

namespace Stu\Component\Player;

enum UserRpgBehaviorEnum: int
{
    case NOT_SET = 0;
    case ACTIVE = 1;
    case OPEN = 2;
    case NONE = 3;

    public function getDescription(): string
    {
        return match ($this) {
            self::NOT_SET =>  _("Der Spieler hat seine Rollenspieleinstellung nicht gesetzt"),
            self::ACTIVE =>  _("Der Spieler betreibt gerne Rollenspiel"),
            self::OPEN =>  _("Der Spieler betreibt gelegentlich Rollenspiel"),
            self::NONE =>  _("Der Spieler betreibt ungern Rollenspiel")
        };
    }
}
