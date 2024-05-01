<?php

namespace Stu\Lib\Pirate;

enum PirateBehaviourEnum: int
{
        // passive
    case DO_NOTHING = 0;
    case FLY = 1;
    case HIDE = 2;
    case SEARCH_FRIEND = 3;

        // aggressive
    case RUB_COLONY = 4;
    case ATTACK_SHIP = 5;
    case RAGE = 6;
    case CALL_FOR_SUPPORT = 7;

        // auxiliary
    case GO_ALERT_RED = 8;
    case DEACTIVATE_SHIELDS = 9;
}
