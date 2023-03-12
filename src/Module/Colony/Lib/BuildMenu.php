<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingEnum;

class BuildMenu
{
    private int $menuId;

    public function __construct(int $menuId)
    {
        $this->menuId = $menuId;
    }

    public function getName(): string
    {
        switch ($this->menuId) {
            case BuildingEnum::BUILDMENU_SOCIAL:
                return _('Soziales');
            case BuildingEnum::BUILDMENU_INDUSTRY:
                return _('Industrie');
            case BuildingEnum::BUILDMENU_INFRASTRUCTURE:
                return _('Infrastruktur');
            default:
                return '';
        }
    }
}
