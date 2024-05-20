<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

//TODO unit tests
final class TholianWebWeaponPhase extends AbstractWeaponPhase implements TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(
        ShipInterface $ship,
        ShipWrapperInterface $wrapper,
        GameControllerInterface $game
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $ship = $wrapper->get();

        $informations->addInformation(sprintf(
            "Das Energienetz um die %s in Sektor %s ist implodiert",
            $ship->getName(),
            $ship->getSectorString()
        ));

        $damage_wrapper = new DamageWrapper(
            (int)ceil(random_int(65, 85) * $wrapper->get()->getMaxHull() / 100)
        );
        $damage_wrapper->setCrit(random_int(0, 3) === 0);
        $damage_wrapper->setShieldDamageFactor(100);
        $damage_wrapper->setHullDamageFactor(100);
        $damage_wrapper->setIsPhaserDamage(true);

        $informations->addInformationWrapper($this->applyDamage->damage($damage_wrapper, $wrapper));

        $this->checkForShipDestruction(
            $ship,
            $wrapper,
            false,
            $informations
        );

        return $informations;
    }
}
