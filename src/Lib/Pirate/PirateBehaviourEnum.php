<?php

namespace Stu\Lib\Pirate;

enum PirateBehaviourEnum: int
{
    case DO_NOTHING = 0;
    case FLY = 1;
    case RUB_COLONY = 2;
    case ATTACK_SHIP = 3;
    case HIDE = 4;
    case RAGE = 5;
    case GO_ALERT_RED = 6;
    case CALL_FOR_SUPPORT = 7;
    case SEARCH_FRIEND = 8;
}
