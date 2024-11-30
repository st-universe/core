<?php

namespace Stu\Module\Config;

use Stu\Module\Config\Model\AdminSettingsInterface;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\Model\ColonySettingsInterface;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\Model\DebugSettingsInterface;
use Stu\Module\Config\Model\EmailSettingsInterface;
use Stu\Module\Config\Model\GameSettingsInterface;
use Stu\Module\Config\Model\MapSettingsInterface;
use Stu\Module\Config\Model\PirateSettingsInterface;
use Stu\Module\Config\Model\ResetSettingsInterface;
use Stu\Module\Config\Model\SqlLoggingSettingsInterface;

enum StuConfigSettingEnum: string
{
    case ADMIN = AdminSettingsInterface::class;
    case CACHE = CacheSettingsInterface::class;
    case COLONY = ColonySettingsInterface::class;
    case DB = DbSettingsInterface::class;
    case DEBUG = DebugSettingsInterface::class;
    case GAME = GameSettingsInterface::class;
    case MAP = MapSettingsInterface::class;
    case RESET = ResetSettingsInterface::class;
    case SQL_LOGGING = SqlLoggingSettingsInterface::class;
    case EMAIL = EmailSettingsInterface::class;
    case PIRATES = PirateSettingsInterface::class;

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
