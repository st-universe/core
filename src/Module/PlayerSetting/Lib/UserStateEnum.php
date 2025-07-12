<?php

namespace Stu\Module\PlayerSetting\Lib;

enum UserStateEnum: int
{
    case USER_STATE_NEW = 0;
    case USER_STATE_UNCOLONIZED = 1;
    case USER_STATE_ACTIVE = 2;
    case USER_STATE_ACCOUNT_VERIFICATION = 3;
    case USER_STATE_COLONIZATION_SHIP = 4;

    public function getDescription(): string
    {
        return match ($this) {
            self::USER_STATE_NEW => _("NEU"),
            self::USER_STATE_UNCOLONIZED => _("OHNE KOLONIEN"),
            self::USER_STATE_ACTIVE => _("AKTIV"),
            self::USER_STATE_ACCOUNT_VERIFICATION => _("ACCOUNT VERIFIKATION"),
            self::USER_STATE_COLONIZATION_SHIP => _("KOLONISATIONS SCHIFF")
        };
    }
}
