<?php

declare(strict_types=1);

namespace Stu\Component\Building;

final class BuildMenuEnum
{
    public const int BUILDMENU_SOCIAL = 1;
    public const int BUILDMENU_INDUSTRY = 2;
    public const int BUILDMENU_INFRASTRUCTURE = 3;
    public const int BUILDMENU_ENERGY = 4;

    public const array BUILDMENU_IDS = [
        BuildMenuEnum::BUILDMENU_SOCIAL,
        BuildMenuEnum::BUILDMENU_INDUSTRY,
        BuildMenuEnum::BUILDMENU_INFRASTRUCTURE,
        BuildMenuEnum::BUILDMENU_ENERGY
    ];

    public static function getDescription(int $menuId): string
    {
        return match ($menuId) {
            BuildMenuEnum::BUILDMENU_SOCIAL => _('Soziales'),
            BuildMenuEnum::BUILDMENU_INDUSTRY => _('Industrie'),
            BuildMenuEnum::BUILDMENU_INFRASTRUCTURE => _('Infrastruktur'),
            BuildMenuEnum::BUILDMENU_ENERGY => _('Energie'),
            default => '',
        };
    }
}
