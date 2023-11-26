<?php

declare(strict_types=1);

namespace Stu\Component\History;

enum HistoryTypeEnum: int
{
    case SHIP = 1;
    case STATION = 2;
    case COLONY = 3;
    case ALLIANCE = 4;
    case OTHER = 5;

    public function getName(): string
    {
        return match ($this) {
            self::SHIP => 'Schiffe',
            self::STATION => 'Station',
            self::COLONY => 'Kolonie',
            self::ALLIANCE => 'Diplomatie',
            self::OTHER => 'Sonstiges'
        };
    }
}
