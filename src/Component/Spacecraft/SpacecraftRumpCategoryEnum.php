<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftRumpCategoryEnum: int
{
    // rump categories
    case RUNABOUT = 1;
    case FRIGATE = 2;
    case ESCORT = 3;
    case DESTROYER = 4;
    case CRUISER = 5;
    case FREIGHT = 6;
    case WARSHIP = 8;
    case ESCAPE_PODS = 9;
    case SHUTTLE = 10;
    case CONSTRUCTION = 11;
    case STATION = 12;
    case ENERGIENETZ = 13;
}
