<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Module\Tal\StatusBarColorEnum;

class HullSystemData extends AbstractSystemData
{
    public function update(): void
    {
        //nothing to do here
    }

    public function getHullStatusBar(): string
    {
        return $this->getTalStatusBar(
            _('Hülle'),
            $this->ship->getHull(),
            $this->ship->getMaxHull(),
            StatusBarColorEnum::STATUSBAR_GREEN
        )
            ->render();
    }

    public function getHullStatusBarBig(): string
    {
        return $this->getTalStatusBar(
            _('Hülle'),
            $this->ship->getHull(),
            $this->ship->getMaxHull(),
            StatusBarColorEnum::STATUSBAR_GREEN
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
