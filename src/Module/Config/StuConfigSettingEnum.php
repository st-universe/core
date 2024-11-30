<?php

namespace Stu\Module\Config;

enum StuConfigSettingEnum: int
{
    case ADMIN = 1;
    case CACHE = 2;
    case COLONY = 3;
    case DB = 4;
    case DEBUG = 5;
    case GAME = 6;
    case MAP = 7;
    case RESET = 8;
    case SQL_LOGGING = 9;
    case EMAIL = 10;
    case PIRATES = 11;

    public function getParent(): ?StuConfigSettingEnum
    {
        return match ($this) {
            self::ADMIN => self::GAME,
            self::CACHE => null,
            self::COLONY => self::GAME,
            self::DB => null,
            self::DEBUG => null,
            self::GAME => null,
            self::MAP => self::GAME,
            self::RESET => null,
            self::SQL_LOGGING => self::DEBUG,
            self::EMAIL => self::GAME,
            self::PIRATES => self::GAME
        };
    }

    public function getConfigPath(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::CACHE => 'cache',
            self::COLONY => 'colony',
            self::DB => 'db',
            self::DEBUG => 'debug',
            self::GAME => 'game',
            self::MAP => 'map',
            self::RESET => 'reset',
            self::SQL_LOGGING => 'sqlLogging',
            self::EMAIL => 'email',
            self::PIRATES => 'pirates'
        };
    }
}
