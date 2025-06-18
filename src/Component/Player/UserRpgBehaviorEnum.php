<?php

declare(strict_types=1);

namespace Stu\Component\Player;

enum UserRpgBehaviorEnum: int
{
    case NOT_SET = 0;
    case ACTIVE = 1;
    case OPEN = 2;
    case NONE = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::NOT_SET =>  "Der Spieler hat seine Rollenspieleinstellung nicht gesetzt",
            self::ACTIVE =>  "Der Spieler betreibt gerne Rollenspiel",
            self::OPEN =>  "Der Spieler betreibt gelegentlich Rollenspiel",
            self::NONE =>  "Der Spieler betreibt ungern Rollenspiel"
        };
    }
}
