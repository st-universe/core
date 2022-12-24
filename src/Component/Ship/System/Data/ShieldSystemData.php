<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Module\Tal\StatusBarColorEnum;

class ShieldSystemData extends AbstractSystemData
{
    public function update(): void
    {
        //nothing to do here
    }

    public function getShieldStatusBar()
    {
        return $this->getTalStatusBar(
            _('Schilde'),
            $this->ship->getShield(),
            $this->ship->getMaxShield(),
            $this->ship->getShieldState() ? StatusBarColorEnum::STATUSBAR_SHIELD_ON : StatusBarColorEnum::STATUSBAR_SHIELD_OFF
        )
            ->render();
    }

    public function getShieldStatusBarBig()
    {
        return $this->getTalStatusBar(
            _('Schilde'),
            $this->ship->getShield(),
            $this->ship->getMaxShield(),
            $this->ship->getShieldState() ? StatusBarColorEnum::STATUSBAR_SHIELD_ON : StatusBarColorEnum::STATUSBAR_SHIELD_OFF
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
