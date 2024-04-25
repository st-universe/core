<?php

namespace Stu\Lib\Pirate;

enum PirateReactionTriggerEnum: int
{
    case ON_ATTACK = 1;
    case ON_SCAN = 2;
    case ON_ALERT_RED = 3;
    case ON_INTERCEPTION = 4;
    case ON_SUPPORT_CALL = 5;
}
