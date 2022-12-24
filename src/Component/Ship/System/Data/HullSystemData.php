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

    public function getHullStatusBar()
    {
        return $this->getTalStatusBar(
            _('Hülle'),
            $this->ship->getHull(),
            $this->ship->getMaxHuell(),
            StatusBarColorEnum::STATUSBAR_GREEN
        )
            ->render();
    }

    public function getHullStatusBarBig()
    {
        return $this->getTalStatusBar(
            _('Hülle'),
            $this->ship->getHull(),
            $this->ship->getMaxHuell(),
            StatusBarColorEnum::STATUSBAR_GREEN
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
