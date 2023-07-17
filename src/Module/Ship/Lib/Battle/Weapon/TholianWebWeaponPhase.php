<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\DamageWrapper;
use Stu\Lib\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

//TODO unit tests
final class TholianWebWeaponPhase extends AbstractWeaponPhase implements TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(ShipWrapperInterface $wrapper, GameControllerInterface $game): InformationWrapper
    {
        $informations = new InformationWrapper();

        $user = $game->getUser();
        $userId = $user->getId();
        $ship = $wrapper->get();

        $$informations->addInformation(sprintf(
            "Das Energienetz um die %s in Sektor %s ist implodiert",
            $ship->getName(),
            $ship->getSectorString()
        ));

        $damage_wrapper = new DamageWrapper(
            (int)ceil(rand(65, 85) * $wrapper->get()->getMaxHull() / 100)
        );
        $damage_wrapper->setCrit(rand(0, 3) === 0);
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
            if ($ship->isBase()) {
                $this->entryCreator->addStationEntry(
                    $entryMsg,
                    $userId
                );
            } else {
                $this->entryCreator->addShipEntry(
                    $entryMsg,
                    $userId
                );
            }

            $this->checkForPrestige($user, $ship);

            $informations->addInformation($this->shipRemover->destroy($wrapper));
        }

        return $informations;
    }
}
