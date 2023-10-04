<?php

declare(strict_types=1);

namespace Stu\Component\Game;

final class GameEnum
{
    //game states
    public const CONFIG_GAMESTATE = 1;
    public const CONFIG_GAMESTATE_VALUE_ONLINE = 1;
    public const CONFIG_GAMESTATE_VALUE_TICK = 2;
    public const CONFIG_GAMESTATE_VALUE_MAINTENANCE = 3;
    public const CONFIG_GAMESTATE_VALUE_RELOCATION = 4;
    public const CONFIG_GAMESTATE_VALUE_RESET = 5;

    //user stuff
    public const USER_ONLINE_PERIOD = 300;

    //trade stuff
    public const MAX_TRADELICENSE_COUNT = 9999;

    //fleet stuff
    public const CREW_PER_FLEET = 100;

    //commnet stuff
    public const KN_PER_SITE = 6;

    // javascript execution
    public const JS_EXECUTION_BEFORE_RENDER = 1;
    public const JS_EXECUTION_AFTER_RENDER = 2;
    public const JS_EXECUTION_AJAX_UPDATE = 3;

    /**
     * Returns the textual representation for a game state
     */
    public static function gameStateTypeToDescription(int $stateId): string
    {
        switch ($stateId) {
            case GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE:
                return 'Online';
            case GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE:
                return 'Wartung';
            case GameEnum::CONFIG_GAMESTATE_VALUE_RESET:
                return 'Reset';
            case GameEnum::CONFIG_GAMESTATE_VALUE_RELOCATION:
                return 'Umzug';
            case GameEnum::CONFIG_GAMESTATE_VALUE_TICK:
                return 'Tick';
            default:
                return '';
        }
    }
}
