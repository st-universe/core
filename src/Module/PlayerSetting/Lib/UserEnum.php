<?php

namespace Stu\Module\PlayerSetting\Lib;

final class UserEnum
{
    // user state
    public const USER_STATE_NEW = 0;
    public const USER_STATE_UNCOLONIZED = 1;
    public const USER_STATE_ACTIVE = 2;
    public const USER_STATE_SMS_VERIFICATION = 3;

    //DELMARK
    public const DELETION_REQUESTED = 1;
    public const DELETION_CONFIRMED = 2;
    public const DELETION_FORBIDDEN = 3;

    //VACATION DELAY, 172800 = 48 hours in seconds
    public const VACATION_DELAY_IN_SECONDS = 172800;

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
        }
        return '';
    }
}
