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

    //user stuff
    public const USER_NOONE = 1;
    public const USER_ONLINE_PERIOD = 300;

    //trade stuff
    public const MAX_TRADELICENCE_COUNT = 7;

    //fleet stuff
    public const CREW_PER_FLEET = 100;

    //commnet stuff
    public const KN_PER_SITE = 6;
}
