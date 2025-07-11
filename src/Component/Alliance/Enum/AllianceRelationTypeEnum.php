<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Enum;

enum AllianceRelationTypeEnum: int
{
    case WAR = 1;
    case PEACE = 2;
    case FRIENDS = 3;
    case ALLIED = 4;
    case TRADE = 5;
    case VASSAL = 6;

    public function getDescription(): string
    {
        return match ($this) {
            self::WAR => 'Krieg',
            self::PEACE => 'Friedensabkommen',
            self::FRIENDS => 'Freundschaftabkommen',
            self::ALLIED => 'Bündnis',
            self::TRADE => 'Handelsabkommen',
            self::VASSAL => 'Vasall'
        };
    }

    /**
     * @return array<array{name: string, value: int}>
     */
    public function getPossibleTypes(): array
    {
        $ret = [];
        if ($this != self::FRIENDS) {
            $ret[] = ["name" => "Freundschaft", "value" => self::FRIENDS->value];
        }
        if ($this != self::ALLIED) {
            $ret[] = ["name" => "Bündnis", "value" => self::ALLIED->value];
        }
        if ($this != self::TRADE) {
            $ret[] = ["name" => "Handelsabkommen", "value" => self::TRADE->value];
        }
        if ($this != self::TRADE) {
            $ret[] = ["name" => "Vasall", "value" => self::VASSAL->value];
        }
        return $ret;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WAR => '#810800',
            self::TRADE => '#a5a200',
            self::PEACE => '#004608',
            self::ALLIED => '#005183',
            self::FRIENDS => '#5cb762',
            self::VASSAL => '#008392'
        };
    }


    /**
     * Returns the image name for relation type visualization
     */
    public function getImage(): string
    {
        return match ($this) {
            self::WAR => 'war_negative',
            self::PEACE, self::FRIENDS => 'friendship_positive',
            self::ALLIED => 'alliance_positive',
            self::TRADE => 'trade_positive',
            self::VASSAL => 'vassal_positive',
        };
    }
}
