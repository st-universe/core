<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ShipInterface;

final class HullShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
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

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //nothing to do here
    }

    public function deactivate(ShipInterface $ship): void
    {
        //nothing to do here
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }
}
