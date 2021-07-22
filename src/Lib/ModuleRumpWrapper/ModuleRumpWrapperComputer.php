<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperComputer extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    public function getValue(): int
    {
        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            current($this->modules)->getModule(),
            'getHitChance',
            $this->rump->getHitChance()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $ship->setHitChance($this->getValue());
    }
}
