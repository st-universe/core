<?php

declare(strict_types=1);

namespace Stu\Component\Building;

enum BuildMenuEnum: int
{
    case BUILDMENU_SOCIAL = 1;
    case BUILDMENU_INDUSTRY = 2;
    case BUILDMENU_INFRASTRUCTURE = 3;
    case BUILDMENU_ENERGY = 4;

    public function getDescription(): string
    {
        return match ($this) {
            self::BUILDMENU_SOCIAL => _('Soziales'),
            self::BUILDMENU_INDUSTRY => _('Industrie'),
            self::BUILDMENU_INFRASTRUCTURE => _('Infrastruktur'),
            self::BUILDMENU_ENERGY => _('Energie'),
        };
    }
}
