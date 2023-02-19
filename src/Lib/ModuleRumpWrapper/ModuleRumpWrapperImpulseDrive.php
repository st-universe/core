<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperImpulseDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(): int
    {
        $moduleValueCalculator = new ModuleValueCalculator();

        $module = current($this->modules);
        if ($module === false) {
            return 0;
        }

        return $moduleValueCalculator->calculateEvadeChance($this->rump, $module->getModule());
    }

    public function apply(ShipInterface $ship): void
    {
        $ship->setEvadeChance($this->getValue());
    }
}
