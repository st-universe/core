<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Component\Game\TimeConstants;

final class UserEnum
{
    //NPC IDs
    public const USER_NOONE = 1;
    public const USER_NPC_FERG = 14;
    public const USER_NPC_KAZON = 17;

    // first user id (below are NPCs)
    public const USER_FIRST_ID = 100;

    // user state
    public const USER_STATE_NEW = 0;
    public const USER_STATE_UNCOLONIZED = 1;
    public const USER_STATE_ACTIVE = 2;
    public const USER_STATE_SMS_VERIFICATION = 3;
    public const USER_STATE_COLONIZATION_SHIP = 4;
    public const USER_STATE_TUTORIAL1 = 5;
    public const USER_STATE_TUTORIAL2 = 6;
    public const USER_STATE_TUTORIAL3 = 7;
    public const USER_STATE_TUTORIAL4 = 8;


    //DELMARK
    public const DELETION_REQUESTED = 1;
    public const DELETION_CONFIRMED = 2;
    public const DELETION_FORBIDDEN = 3;
    public const DELETION_EXECUTED = 4;

    //VACATION DELAY
    public const VACATION_DELAY_IN_SECONDS = TimeConstants::TWO_DAYS_IN_SECONDS;

    public static function getUserStateDescription(int $userState): string
    {
        switch ($userState) {
            case self::USER_STATE_NEW:
                return _("NEU");
            case self::USER_STATE_UNCOLONIZED:
                return _("OHNE KOLONIEN");
            case self::USER_STATE_ACTIVE:
                return _("AKTIV");
            case self::USER_STATE_SMS_VERIFICATION:
                return _("SMS VERIFIKATION");
            case self::USER_STATE_COLONIZATION_SHIP:
                return _("KOLONISATIONS SCHIFF");
            case self::USER_STATE_TUTORIAL1:
                return _("TUTORIAL GEBÄUDE");
            case self::USER_STATE_TUTORIAL2:
                return _("TUTORIAL FORSCHUNG");
            case self::USER_STATE_TUTORIAL3:
                return _("TUTORIAL SCHIFFE");
            case self::USER_STATE_TUTORIAL4:
                return _("TUTORIAL HANDEL");
        }
        return '';
    }
}
