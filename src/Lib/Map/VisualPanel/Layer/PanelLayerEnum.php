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
    case SHIP_COUNT = 6;
    case BORDER = 7;
}
