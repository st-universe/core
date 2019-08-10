<?php

declare(strict_types=1);

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
            case BUILDMENU_SOCIAL:
                return _('Soziales');
            case BUILDMENU_INDUSTRY:
                return _('Industrie');
            case BUILDMENU_INFRASTRUCTURE;
                return _('Infrastruktur');
        }
    }


}