<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Component\Game\TimeConstants;

final class UserEnum
{
    //NPC IDs
    public const int USER_NOONE = 1;
    public const int USER_FOREIGN_BUILDPLANS = 4;
    public const int USER_NPC_FEDERATION = 10;
    public const int USER_NPC_ROMULAN = 11;
    public const int USER_NPC_KLINGON = 12;
    public const int USER_NPC_CARDASSIAN = 13;
    public const int USER_NPC_FERG = 14;
    public const int USER_NPC_KAZON = 17;

    // first user id (below are NPCs)
    public const int USER_FIRST_ID = 100;

    //DELMARK
    public const int DELETION_REQUESTED = 1;
    public const int DELETION_CONFIRMED = 2;
    public const int DELETION_FORBIDDEN = 3;
    public const int DELETION_EXECUTED = 4;

    //VACATION DELAY
    public const int VACATION_DELAY_IN_SECONDS = TimeConstants::TWO_DAYS_IN_SECONDS;
}
