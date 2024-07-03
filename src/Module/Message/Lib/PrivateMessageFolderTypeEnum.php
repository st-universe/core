<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

enum PrivateMessageFolderTypeEnum: int
{
    //default
    case DEFAULT_OWN = 0;

    //special categories
    case SPECIAL_MAIN = 1;
    case SPECIAL_SHIP = 2;
    case SPECIAL_COLONY = 3;
    case SPECIAL_TRADE = 4;
    case SPECIAL_SYSTEM = 5;
    case SPECIAL_PMOUT = 6;
    case SPECIAL_STATION = 7;

    public function isDefault(): bool
    {
        return match ($this) {
            self::DEFAULT_OWN => false,
            self::SPECIAL_MAIN => true,
            self::SPECIAL_SHIP => true,
            self::SPECIAL_COLONY => true,
            self::SPECIAL_TRADE => true,
            self::SPECIAL_SYSTEM => true,
            self::SPECIAL_PMOUT => true,
            self::SPECIAL_STATION => true
        };
    }

    /**
     * specifies if you can move a private message to this folder
     */
    public function isDropable(): bool
    {
        return match ($this) {
            self::DEFAULT_OWN => true,
            self::SPECIAL_MAIN => true,
            self::SPECIAL_SHIP => false,
            self::SPECIAL_COLONY => false,
            self::SPECIAL_TRADE => false,
            self::SPECIAL_SYSTEM => false,
            self::SPECIAL_PMOUT => false,
            self::SPECIAL_STATION => false
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DEFAULT_OWN => 'Eigene',
            self::SPECIAL_MAIN => 'Persönlich',
            self::SPECIAL_SHIP => 'Schiffe',
            self::SPECIAL_COLONY => 'Kolonien',
            self::SPECIAL_TRADE => 'Handel',
            self::SPECIAL_SYSTEM => 'Systemmeldungen',
            self::SPECIAL_PMOUT => 'Postausgang',
            self::SPECIAL_STATION => 'Stationen',
        };
    }
}
