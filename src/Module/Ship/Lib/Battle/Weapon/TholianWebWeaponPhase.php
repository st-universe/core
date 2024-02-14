<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

//TODO unit tests
final class TholianWebWeaponPhase extends AbstractWeaponPhase implements TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(ShipWrapperInterface $wrapper, GameControllerInterface $game, $webUser): InformationWrapper
    {
        $informations = new InformationWrapper();

        $user = $game->getUser();
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

        if ($ship->isDestroyed()) {
            $entryMsg = sprintf(
                'Die %s (%s) wurde in Sektor %s durch ein implodierendes Energienetz zerstört',
                $ship->getName(),
                $ship->getRump()->getName(),
                $ship->getSectorString()
            );

            $this->entryCreator->addEntry(
                $entryMsg,
                $user->getId(),
                $ship
            );

            $this->checkForPrestige($user, $ship);

            $informations->addInformation($this->shipRemover->destroy($wrapper));
        }

        return $informations;
    }
}
