<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperImpulseDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    public function getValue(): int
    {
        return calculateEvadeChance($this->rump, current($this->modules)->getModule());
    }

    public function apply(ShipInterface $ship): void
    {
        $ship->setEvadeChance($this->getValue());
    }
}