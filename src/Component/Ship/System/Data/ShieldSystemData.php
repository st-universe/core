<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Template\StatusBarColorEnum;

class ShieldSystemData extends AbstractSystemData
{
    #[Override]
    public function update(): void
    {
        //nothing to do here
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SHIELDS;
    }

    public function getShieldStatusBar(): string
    {
        return $this->getStatusBar(
            _('Schilde'),
            $this->ship->getShield(),
            $this->ship->getMaxShield(),
            $this->ship->getShieldState() ? StatusBarColorEnum::STATUSBAR_SHIELD_ON : StatusBarColorEnum::STATUSBAR_SHIELD_OFF
        )
            ->render();
    }

    public function getShieldStatusBarBig(): String
    {
        return $this->getStatusBar(
            _('Schilde'),
            $this->ship->getShield(),
            $this->ship->getMaxShield(),
            $this->ship->getShieldState() ? StatusBarColorEnum::STATUSBAR_SHIELD_ON : StatusBarColorEnum::STATUSBAR_SHIELD_OFF
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
