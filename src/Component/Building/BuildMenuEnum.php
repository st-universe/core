<?php

declare(strict_types=1);

namespace Stu\Component\Building;

final class BuildMenuEnum
{
    public const BUILDMENU_SOCIAL = 1;
    public const BUILDMENU_INDUSTRY = 2;
    public const BUILDMENU_INFRASTRUCTURE = 3;
    public const BUILDMENU_ENERGY = 4;

    public const BUILDMENU_IDS = [
        BuildMenuEnum::BUILDMENU_SOCIAL,
        BuildMenuEnum::BUILDMENU_INDUSTRY,
        BuildMenuEnum::BUILDMENU_INFRASTRUCTURE,
        BuildMenuEnum::BUILDMENU_ENERGY
    ];

    public static function getDescription(int $menuId): string
    {
        switch ($menuId) {
            case BuildMenuEnum::BUILDMENU_SOCIAL:
                return _('Soziales');
            case BuildMenuEnum::BUILDMENU_INDUSTRY:
                return _('Industrie');
            case BuildMenuEnum::BUILDMENU_INFRASTRUCTURE:
                return _('Infrastruktur');
            case BuildMenuEnum::BUILDMENU_ENERGY:
                return _('Energie');
            default:
                return '';
        }
    }
}
