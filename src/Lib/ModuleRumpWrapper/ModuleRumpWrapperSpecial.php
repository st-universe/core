<?php

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperSpecial extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    public function getValue(): int
    {
        return 0;
    }

    public function apply(ShipInterface $ship): void
    {
        //foreach ($this->modules as $module) {
            //if ($module->getModule()->hasSpecial(ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK)) {
            //    $ship->setCloakable(true);
            //}
        //}
    }
}
