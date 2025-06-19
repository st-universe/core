<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftRumpCategoryEnum: int
{
    // rump categories
    case SHIP_CATEGORY_RUNABOUT = 1;
    case SHIP_CATEGORY_FRIGATE = 2;
    case SHIP_CATEGORY_ESCORT = 3;
    case SHIP_CATEGORY_DESTROYER = 4;
    case SHIP_CATEGORY_CRUISER = 5;
    case SHIP_CATEGORY_FREIGHT = 6;
    case SHIP_CATEGORY_WARSHIP = 8;
    case SHIP_CATEGORY_ESCAPE_PODS = 9;
    case SHIP_CATEGORY_SHUTTLE = 10;
    case SHIP_CATEGORY_CONSTRUCTION = 11;
    case SHIP_CATEGORY_STATION = 12;
}
