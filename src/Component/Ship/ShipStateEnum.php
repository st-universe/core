<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipStateEnum
{

    public const SHIP_STATE_NONE = 0;
    public const SHIP_STATE_REPAIR_PASSIVE = 1;
    public const SHIP_STATE_SYSTEM_MAPPING = 2;
    public const SHIP_STATE_UNDER_CONSTRUCTION = 3;
    public const SHIP_STATE_REPAIR_ACTIVE = 4;
    public const SHIP_STATE_UNDER_SCRAPPING = 5;

    public static function getDescription(int $state): string
    {
        switch ($state) {
            case self::SHIP_STATE_NONE:
                return _("0_none");
            case self::SHIP_STATE_REPAIR_PASSIVE:
                return _("1_repair_passive");
            case self::SHIP_STATE_SYSTEM_MAPPING:
                return _("2_system_mapping");
            case self::SHIP_STATE_UNDER_CONSTRUCTION:
                return _("3_under_construction");
            case self::SHIP_STATE_REPAIR_ACTIVE:
                return _("4_repair_active");
            case self::SHIP_STATE_UNDER_SCRAPPING:
                return _("5_scrapping");
        }
        return 'unknown';
    }
}
