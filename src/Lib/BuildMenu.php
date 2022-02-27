<?php

declare(strict_types=1);

use Stu\Component\Building\BuildingEnum;

class BuildMenu
{

    private $menuId = 0;

    /**
     */
    function __construct($menuId)
    {
        $this->menuId = $menuId;
    }

    /**
     */
    public function getName()
    {
        switch ($this->menuId) {
            case BuildingEnum::BUILDMENU_SOCIAL:
                return _('Soziales');
            case BuildingEnum::BUILDMENU_INDUSTRY:
                return _('Industrie');
            case BuildingEnum::BUILDMENU_INFRASTRUCTURE:
                return _('Infrastruktur');
        }
    }
}
