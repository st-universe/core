<?php

declare(strict_types=1);

namespace Stu\Component\Game;

final class GameEnum
{
    //game states
    public const int CONFIG_GAMESTATE = 1;
    public const int CONFIG_GAMESTATE_VALUE_ONLINE = 1;
    public const int CONFIG_GAMESTATE_VALUE_MAINTENANCE = 3;
    public const int CONFIG_GAMESTATE_VALUE_RELOCATION = 4;
    public const int CONFIG_GAMESTATE_VALUE_RESET = 5;

    //user stuff
    public const int USER_ONLINE_PERIOD = 300;

    //trade stuff
    public const int MAX_TRADELICENSE_COUNT = 9999;

    //fleet stuff
    public const int CREW_PER_FLEET = 100;

    //commnet stuff
    public const int KN_PER_SITE = 6;

    // javascript execution
    public const int JS_EXECUTION_BEFORE_RENDER = 1;
    public const int JS_EXECUTION_AFTER_RENDER = 2;
    public const int JS_EXECUTION_AJAX_UPDATE = 3;

    /**
     * Returns the textual representation for a game state
     */
    public static function gameStateTypeToDescription(int $stateId): string
    {
        return match ($stateId) {
            GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE => 'Online',
            GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE => 'Wartung',
            GameEnum::CONFIG_GAMESTATE_VALUE_RESET => 'Reset',
            GameEnum::CONFIG_GAMESTATE_VALUE_RELOCATION => 'Umzug',
            default => '',
        };
    }
}
