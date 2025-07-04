<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

enum PanelLayerEnum: int
{
    case BACKGROUND = 1;
    case SYSTEM = 2;
    case MAP = 3;
    case COLONY_SHIELD = 4;
    case SUBSPACE_SIGNATURES = 5;
    case SPACECRAFT_SIGNATURE = 6;
    case SPACECRAFT_COUNT = 7;
    case BORDER = 8;
    case ANOMALIES = 9;

    public function isAffectedByLssBlockade(): bool
    {
        return match ($this) {
            self::MAP,
            self::SYSTEM => false,
            default => true
        };
    }
}
