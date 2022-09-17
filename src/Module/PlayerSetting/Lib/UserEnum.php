<?php

namespace Stu\Module\PlayerSetting\Lib;

use Stu\Component\Game\TimeConstants;

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
        }
        return '';
    }
}
