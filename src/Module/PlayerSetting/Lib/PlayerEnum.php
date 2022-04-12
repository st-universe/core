<?php

namespace Stu\Module\PlayerSetting\Lib;

final class PlayerEnum
{
    // user state
    public const USER_NEW = 0;
    public const USER_UNCOLONIZED = 1;
    public const USER_ACTIVE = 2;
    public const USER_LOCKED = 4;

    //DELMARK
    public const DELETION_REQUESTED = 1;
    public const DELETION_CONFIRMED = 2;
    public const DELETION_FORBIDDEN = 3;

    //VACATION DELAY, 172800 = 48 hours in seconds
    public const VACATION_DELAY_IN_SECONDS = 172800;

    public static function getUserStateDescription(int $userState): string
    {
        switch ($userState) {
            case self::USER_NEW:
                return _("NEU");
            case self::USER_UNCOLONIZED:
                return _("OHNE KOLONIEN");
            case self::USER_ACTIVE:
                return _("AKTIV");
            case self::USER_LOCKED:
                return _("GESPERRT");
        }
        return '';
    }
}
