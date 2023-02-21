<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Orm\Entity\AllianceRelationInterface;

final class AllianceEnum
{

    /**
     * @var int
     */
    public const ALLIANCE_JOBS_FOUNDER = 1;

    /**
     * @var int
     */
    public const ALLIANCE_JOBS_SUCCESSOR = 2;

    /**
     * @var int
     */
    public const ALLIANCE_JOBS_DIPLOMATIC = 3;

    /**
     * @var int
     */
    public const ALLIANCE_JOBS_PENDING = 4;

    /**
     * @var int
     */
    public const ALLIANCE_RELATION_WAR = 1;

    /**
     * @var int
     */
    public const ALLIANCE_RELATION_PEACE = 2;

    /**
     * @var int
     */
    public const ALLIANCE_RELATION_FRIENDS = 3;

    /**
     * @var int
     */
    public const ALLIANCE_RELATION_ALLIED = 4;

    /**
     * @var int
     */
    public const ALLIANCE_RELATION_TRADE = 5;

    /**
     * @var int
     */
    public const ALLIANCE_RELATION_VASSAL = 6;

    /** @var list<int> */
    public const ALLOWED_RELATION_TYPES = [
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
        switch ($relationType) {
            case AllianceEnum::ALLIANCE_RELATION_WAR:
                return '#810800';
            case AllianceEnum::ALLIANCE_RELATION_TRADE:
                return '#a5a200';
            case AllianceEnum::ALLIANCE_RELATION_PEACE:
                return '#004608';
            case AllianceEnum::ALLIANCE_RELATION_ALLIED:
                return '#005183';
            case AllianceEnum::ALLIANCE_RELATION_FRIENDS:
                return '#5cb762';
            case AllianceEnum::ALLIANCE_RELATION_VASSAL:
                return '#008392';
            default:
                return '#ffffff';
        }
    }

    public static function relationTypeToDescription(
        int $relationType
    ): string {
        switch ($relationType) {
            case AllianceEnum::ALLIANCE_RELATION_WAR:
                return 'Krieg';
            case AllianceEnum::ALLIANCE_RELATION_PEACE:
                return 'Friedensabkommen';
            case AllianceEnum::ALLIANCE_RELATION_FRIENDS:
                return 'Freundschaftabkommen';
            case AllianceEnum::ALLIANCE_RELATION_ALLIED:
                return 'Bündnis';
            case AllianceEnum::ALLIANCE_RELATION_TRADE:
                return 'Handelsabkommen';
            case AllianceEnum::ALLIANCE_RELATION_VASSAL:
                return 'Vasall';
        }
        return '';
    }
}