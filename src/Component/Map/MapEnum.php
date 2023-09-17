<?php

declare(strict_types=1);

namespace Stu\Component\Map;

final class MapEnum
{
    //DEFAULT LAYER
    public const DEFAULT_LAYER = self::LAYER_ID_TULLAMORE;

    //LAYERS
    public const LAYER_ID_WORMHOLES = 0;
    public const LAYER_ID_CRAGGANMORE = 1;
    public const LAYER_ID_TULLAMORE = 2;

    //SECTIONS
    public const FIELDS_PER_SECTION = 20;

    //WORMHOLE ENTRY TYPES
    public const WORMHOLE_ENTRY_TYPE_IN = 0;
    public const WORMHOLE_ENTRY_TYPE_OUT = 1;
    public const WORMHOLE_ENTRY_TYPE_BOTH = 2;

    //OTHER
    public const MAPTYPE_INSERT = 1;
    public const MAPTYPE_LAYER_EXPLORED = 2;
}
