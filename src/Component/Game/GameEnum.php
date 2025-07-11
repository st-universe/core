<?php

declare(strict_types=1);

namespace Stu\Component\Game;

final class GameEnum
{
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
}
