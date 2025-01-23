<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

enum AnomalyTypeEnum: int
{
        // anomaly types
    case SUBSPACE_ELLIPSE = 1;
    case ION_STORM = 2;

        // special anomalies
    case SPECIAL_ADVENT_DOOR = 1001;

    public function getTemplate(): string
    {
        return match ($this) {
            self::SPECIAL_ADVENT_DOOR => 'html/anomaly/adventDoor.twig',
            default => 'html/anomaly/standardAnomaly.twig'
        };
    }
}
