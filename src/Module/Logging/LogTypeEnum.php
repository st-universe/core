<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

enum LogTypeEnum: string
{
    case DEFAULT = 'stu';
    case PIRATE = 'pirate';
    case ANOMALY = 'anomaly';
    case DBAL = 'dbal';
    case TICK = 'tick';

    public function isRotating(): bool
    {
        return match ($this) {
            self::DEFAULT,
            self::TICK => false,
            default => true
        };
    }

    public function getLogfilePath(string $logFolder): string
    {
        $subPath = match ($this) {
            self::DEFAULT => '/debug.log',
            self::PIRATE => '/pirate/pirate.log',
            self::ANOMALY => '/anomaly/anomaly.log',
            self::DBAL => '/dbal/sql.log',
            self::TICK => '/tick.log'
        };

        return sprintf('%s%s', $logFolder, $subPath);
    }
}
