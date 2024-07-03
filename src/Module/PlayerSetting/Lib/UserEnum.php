<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Component\Game\TimeConstants;

final class UserEnum
{
    //NPC IDs
    public const int USER_NOONE = 1;
    public const int USER_NPC_FERG = 14;
    public const int USER_NPC_KAZON = 17;

    // first user id (below are NPCs)
    public const int USER_FIRST_ID = 100;

    // user state
    public const int USER_STATE_NEW = 0;
    public const int USER_STATE_UNCOLONIZED = 1;
    public const int USER_STATE_ACTIVE = 2;
    public const int USER_STATE_SMS_VERIFICATION = 3;
    public const int USER_STATE_COLONIZATION_SHIP = 4;
    public const int USER_STATE_TUTORIAL1 = 5;
    public const int USER_STATE_TUTORIAL2 = 6;
    public const int USER_STATE_TUTORIAL3 = 7;
    public const int USER_STATE_TUTORIAL4 = 8;


    //DELMARK
    public const int DELETION_REQUESTED = 1;
    public const int DELETION_CONFIRMED = 2;
    public const int DELETION_FORBIDDEN = 3;
    public const int DELETION_EXECUTED = 4;

    //VACATION DELAY
    public const int VACATION_DELAY_IN_SECONDS = TimeConstants::TWO_DAYS_IN_SECONDS;

    public static function getUserStateDescription(int $userState): string
    {
        return match ($userState) {
            self::USER_STATE_NEW => _("NEU"),
            self::USER_STATE_UNCOLONIZED => _("OHNE KOLONIEN"),
            self::USER_STATE_ACTIVE => _("AKTIV"),
            self::USER_STATE_SMS_VERIFICATION => _("SMS VERIFIKATION"),
            self::USER_STATE_COLONIZATION_SHIP => _("KOLONISATIONS SCHIFF"),
            self::USER_STATE_TUTORIAL1 => _("TUTORIAL GEBÄUDE"),
            self::USER_STATE_TUTORIAL2 => _("TUTORIAL FORSCHUNG"),
            self::USER_STATE_TUTORIAL3 => _("TUTORIAL SCHIFFE"),
            self::USER_STATE_TUTORIAL4 => _("TUTORIAL HANDEL"),
            default => '',
        };
    }
}
