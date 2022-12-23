<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperEps extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    public function getValue(): int
    {
        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            current($this->modules)->getModule(),
            null,
            $this->rump->getBaseEps()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $this->wrapper->getEpsShipSystem()->setMaxEps($this->getValue())->update();
    }
}
