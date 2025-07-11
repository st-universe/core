<?php

declare(strict_types=1);

namespace Stu\Component\Game;

enum GameStateEnum: int
{
    case ONLINE = 1;
    case TICK = 2;
    case MAINTENANCE = 3;
    case RELOCATION = 4;
    case RESET = 5;

    /**
     * Returns the textual representation for a game state
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::ONLINE => 'Online',
            self::TICK => 'Tick',
            self::MAINTENANCE => 'Wartung',
            self::RESET => 'Reset',
            self::RELOCATION => 'Umzug'
        };
    }
}
