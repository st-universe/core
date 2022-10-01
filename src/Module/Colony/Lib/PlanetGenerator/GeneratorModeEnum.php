<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\PlanetGenerator;

final class GeneratorModeEnum
{
    //field placing modes
    public const TOP_LEFT = "top left";
    public const POLAR = "polar";
    public const STRICT_POLAR = "strict polar";
    public const POLAR_SEEDING_NORTH = "polar seeding north";
    public const POLAR_SEEDING_SOUTH = "polar seeding south";
    public const EQUATORIAL = "equatorial";
    public const NO_CLUSTER = "nocluster";
    public const FORCED_ADJACENCY = "forced adjacency";
    public const FORCED_RIM = "forced rim";
    public const LOWER_ORBIT = "lower orbit";
    public const UPPER_ORBIT = "upper orbit";
    public const TIDAL_SEEDING = "tidal seeding";
    public const RIGHT = "right";
    public const BELOW = "below";
    public const CRATER_SEEDING = "crater seeding";
    public const FULL_SURFACE = "fullsurface";
}
