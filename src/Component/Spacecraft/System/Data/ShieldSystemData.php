<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarColorEnum;

class ShieldSystemData extends AbstractSystemData
{
    #[Override]
    public function update(): void
    {
        //nothing to do here
    }

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SHIELDS;
    }

    public function getShieldStatusBar(): string
    {
        return $this->getStatusBar(
            _('Schilde'),
            $this->spacecraft->getShield(),
            $this->spacecraft->getMaxShield(),
            $this->spacecraft->getShieldState() ? StatusBarColorEnum::STATUSBAR_SHIELD_ON : StatusBarColorEnum::STATUSBAR_SHIELD_OFF
        )
            ->render();
    }

    public function getShieldStatusBarBig(): String
    {
        return $this->getStatusBar(
            _('Schilde'),
            $this->spacecraft->getShield(),
            $this->spacecraft->getMaxShield(),
            $this->spacecraft->getShieldState() ? StatusBarColorEnum::STATUSBAR_SHIELD_ON : StatusBarColorEnum::STATUSBAR_SHIELD_OFF
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
