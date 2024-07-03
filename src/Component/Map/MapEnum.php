<?php

declare(strict_types=1);

namespace Stu\Component\Map;

final class MapEnum
{
    //DEFAULT LAYER
    public const int DEFAULT_LAYER = self::LAYER_ID_TULLAMORE;

    //LAYERS
    public const int LAYER_ID_CRAGGANMORE = 1;
    public const int LAYER_ID_TULLAMORE = 2;

    //SECTIONS
    public const int FIELDS_PER_SECTION = 20;

    //WORMHOLE ENTRY TYPES
    public const int WORMHOLE_ENTRY_TYPE_IN = 0;
    public const int WORMHOLE_ENTRY_TYPE_OUT = 1;
    public const int WORMHOLE_ENTRY_TYPE_BOTH = 2;

    //OTHER
    public const int MAPTYPE_INSERT = 1;
    public const int MAPTYPE_LAYER_EXPLORED = 2;

    //MAP REGIONS
    public const int ADMIN_REGION_SUPERPOWER_CENTRAL = 61;
    public const int ADMIN_REGION_SUPERPOWER_PERIPHERAL = 62;
}
