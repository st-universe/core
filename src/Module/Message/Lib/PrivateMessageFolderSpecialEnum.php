<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

//TODO convert to enum
final class PrivateMessageFolderSpecialEnum
{
    //default
    public const PM_DEFAULT_OWN = 0;

    //special categories
    public const PM_SPECIAL_MAIN = 1;
    public const PM_SPECIAL_SHIP = 2;
    public const PM_SPECIAL_COLONY = 3;
    public const PM_SPECIAL_TRADE = 4;
    public const PM_SPECIAL_SYSTEM = 5;
    public const PM_SPECIAL_PMOUT = 6;
    public const PM_SPECIAL_STATION = 7;

    public const DEFAULT_CATEGORIES = [
        self::PM_SPECIAL_MAIN => 'Persönlich',
        self::PM_SPECIAL_SHIP => 'Schiffe',
        self::PM_SPECIAL_STATION => 'Stationen',
        self::PM_SPECIAL_COLONY => 'Kolonien',
        self::PM_SPECIAL_TRADE => 'Handel',
        self::PM_SPECIAL_SYSTEM => 'Systemmeldungen',
        self::PM_SPECIAL_PMOUT => 'Postausgang'
    ];
}
