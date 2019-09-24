<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperHull extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(): int
    {
        return calculateModuleValue(
            $this->rump,
            current($this->modules)->getModule(),
            null,
            $this->rump->getBaseHull()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $value = $this->getValue();
        $ship->setMaxHuell($value);
        $ship->setHuell($value);
    }
}