<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

final class AllianceEnum
{
    /**
     * @var int
     */
    public const int ALLIANCE_JOBS_FOUNDER = 1;

    /**
     * @var int
     */
    public const int ALLIANCE_JOBS_SUCCESSOR = 2;

    /**
     * @var int
     */
    public const int ALLIANCE_JOBS_DIPLOMATIC = 3;

    /**
     * @var int
     */
    public const int ALLIANCE_JOBS_PENDING = 4;

    /**
     * @var int
     */
    public const int ALLIANCE_RELATION_WAR = 1;

    /**
     * @var int
     */
    public const int ALLIANCE_RELATION_PEACE = 2;

    /**
     * @var int
     */
    public const int ALLIANCE_RELATION_FRIENDS = 3;

    /**
     * @var int
     */
    public const int ALLIANCE_RELATION_ALLIED = 4;

    /**
     * @var int
     */
    public const int ALLIANCE_RELATION_TRADE = 5;

    /**
     * @var int
     */
    public const int ALLIANCE_RELATION_VASSAL = 6;

    /** @var list<int> */
    public const array ALLOWED_RELATION_TYPES = [
        AllianceEnum::ALLIANCE_RELATION_WAR,
        AllianceEnum::ALLIANCE_RELATION_PEACE,
        AllianceEnum::ALLIANCE_RELATION_FRIENDS,
        AllianceEnum::ALLIANCE_RELATION_ALLIED,
        AllianceEnum::ALLIANCE_RELATION_TRADE,
        AllianceEnum::ALLIANCE_RELATION_VASSAL
    ];

    public static function relationTypeToColor(
        int $relationType
    ): string {
        return match ($relationType) {
            AllianceEnum::ALLIANCE_RELATION_WAR => '#810800',
            AllianceEnum::ALLIANCE_RELATION_TRADE => '#a5a200',
            AllianceEnum::ALLIANCE_RELATION_PEACE => '#004608',
            AllianceEnum::ALLIANCE_RELATION_ALLIED => '#005183',
            AllianceEnum::ALLIANCE_RELATION_FRIENDS => '#5cb762',
            AllianceEnum::ALLIANCE_RELATION_VASSAL => '#008392',
            default => '#ffffff',
        };
    }

    public static function relationTypeToDescription(int $relationType): string
    {
        return match ($relationType) {
            AllianceEnum::ALLIANCE_RELATION_WAR => 'Krieg',
            AllianceEnum::ALLIANCE_RELATION_PEACE => 'Friedensabkommen',
            AllianceEnum::ALLIANCE_RELATION_FRIENDS => 'Freundschaftabkommen',
            AllianceEnum::ALLIANCE_RELATION_ALLIED => 'Bündnis',
            AllianceEnum::ALLIANCE_RELATION_TRADE => 'Handelsabkommen',
            AllianceEnum::ALLIANCE_RELATION_VASSAL => 'Vasall',
            default => '',
        };
    }
}
