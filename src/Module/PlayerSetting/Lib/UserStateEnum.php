<?php

namespace Stu\Module\PlayerSetting\Lib;

enum UserStateEnum: int
{
    case NEW = 0;
    case UNCOLONIZED = 1;
    case ACTIVE = 2;
    case ACCOUNT_VERIFICATION = 3;
    case COLONIZATION_SHIP = 4;

    public function getDescription(): string
    {
        return match ($this) {
            self::NEW => "NEU",
            self::UNCOLONIZED => "OHNE KOLONIEN",
            self::ACTIVE => "AKTIV",
            self::ACCOUNT_VERIFICATION => "ACCOUNT VERIFIKATION",
            self::COLONIZATION_SHIP => "KOLONISATIONS SCHIFF"
        };
    }
}
