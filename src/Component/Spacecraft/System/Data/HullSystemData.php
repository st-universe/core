<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarColorEnum;

class HullSystemData extends AbstractSystemData
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::HULL;
    }

    public function getHullStatusBar(): string
    {
        return $this->getStatusBar(
            _('Hülle'),
            $this->spacecraft->getCondition()->getHull(),
            $this->spacecraft->getMaxHull(),
            StatusBarColorEnum::GREEN
        )
            ->render();
    }

    public function getHullStatusBarBig(): string
    {
        return $this->getStatusBar(
            _('Hülle'),
            $this->spacecraft->getCondition()->getHull(),
            $this->spacecraft->getMaxHull(),
            StatusBarColorEnum::GREEN
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
