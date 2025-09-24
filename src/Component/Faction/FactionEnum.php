<?php

declare(strict_types=1);

namespace Stu\Component\Faction;

use InvalidArgumentException;
use RuntimeException;

enum FactionEnum: int
{
    case FACTION_FEDERATION = 1;
    case FACTION_ROMULAN = 2;
    case FACTION_KLINGON = 3;
    case FACTION_CARDASSIAN = 4;
    case FACTION_FERENGI = 5;
    case FACTION_PAKLED = 6;
    case FACTION_KAZON = 7;
    case FACTION_BORG = 8;
    case FACTION_BLANK = 9;
    case FACTION_HIROGEN = 10;

    public function getColorCode(): string
    {
        return match ($this) {
            self::FACTION_FEDERATION => '#0000ff',
            self::FACTION_ROMULAN => '#00ff00',
            self::FACTION_KLINGON => '#ff0000',
            self::FACTION_CARDASSIAN => '#ff7b42',
            self::FACTION_FERENGI => '#943100',
            default => throw new RuntimeException(sprintf('no color code defined for %s', $this->name))
        };
    }

    public static function fromName(string $name): FactionEnum
    {
        return match ($name) {
            'federation' => self::FACTION_FEDERATION,
            'romulan' => self::FACTION_ROMULAN,
            'klingon' => self::FACTION_KLINGON,
            'cardassian' => self::FACTION_CARDASSIAN,
            'ferengi' => self::FACTION_FERENGI,
            default => throw new InvalidArgumentException(sprintf('no faction defined for name %s', $name))
        };
    }
}
