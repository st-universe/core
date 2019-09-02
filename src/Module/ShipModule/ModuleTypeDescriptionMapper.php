<?php

namespace Stu\Module\ShipModule;

class ModuleTypeDescriptionMapper
{

    static function getDescription($type): string
    {
        switch ($type) {
            case MODULE_TYPE_HULL:
                return _("Hülle");
            case MODULE_TYPE_SHIELDS:
                return _("Schilde");
            case MODULE_TYPE_EPS:
                return _("EPS-Leitungen");
            case MODULE_TYPE_IMPULSEDRIVE:
                return _("Antrieb");
            case MODULE_TYPE_WARPCORE:
                return _("Reaktor");
            case MODULE_TYPE_COMPUTER:
                return _("Computer");
            case MODULE_TYPE_PHASER:
                return _("Energiewaffe");
            case MODULE_TYPE_TORPEDO:
                return _("Torpedobank");
            case MODULE_TYPE_SPECIAL:
                return _("Spezial");
        }
        return '';
    }
}
