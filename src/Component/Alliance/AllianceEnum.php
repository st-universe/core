<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Orm\Entity\AllianceRelationInterface;

final class AllianceEnum
{

    public const ALLIANCE_JOBS_FOUNDER = 1;
    public const ALLIANCE_JOBS_SUCCESSOR = 2;
    public const ALLIANCE_JOBS_DIPLOMATIC = 3;
    public const ALLIANCE_JOBS_PENDING = 4;
    public const ALLIANCE_RELATION_WAR = 1;
    public const ALLIANCE_RELATION_PEACE = 2;
    public const ALLIANCE_RELATION_FRIENDS = 3;
    public const ALLIANCE_RELATION_ALLIED = 4;
    public const ALLIANCE_RELATION_TRADE = 5;
    public const ALLIANCE_RELATION_VASSAL = 6;

    public static function relationTypeToColor(
        AllianceRelationInterface $relation
    ): string {
        switch ($relation->getType()) {
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
}