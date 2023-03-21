<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;

interface AttackerProviderFactoryInterface
{
    public function getShipAttacker(ShipWrapperInterface $wrapper): ShipAttacker;
    public function getEnergyPhalanxAttacker(ColonyInterface $colony): EnergyAttackerInterface;
    public function getProjectilePhalanxAttacker(ColonyInterface $colony): ProjectileAttackerInterface;
}
