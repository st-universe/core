<?php

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperSpecial extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(): int
    {
        return 0;
    }

    public function apply(ShipInterface $ship): void
    {
    }
}
