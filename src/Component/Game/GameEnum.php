<?php

declare(strict_types=1);

namespace Stu\Component\Game;

final class GameEnum
{

    public const CONFIG_GAMESTATE = 1;
    public const CONFIG_GAMESTATE_VALUE_ONLINE = 1;
    public const CONFIG_GAMESTATE_VALUE_TICK = 2;
    public const CONFIG_GAMESTATE_VALUE_MAINTENANCE = 3;
    public const USER_NOONE = 1;
    public const USER_ONLINE_PERIOD = 300;
    public const MAX_TRADELICENCE_COUNT = 6;
    public const CREW_PER_FLEET = 100;
}
