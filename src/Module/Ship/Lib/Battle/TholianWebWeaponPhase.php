<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\DamageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class TholianWebWeaponPhase extends AbstractWeaponPhase implements TholianWebWeaponPhaseInterface
{
    public function damageCapturedShip(ShipWrapperInterface $wrapper, GameControllerInterface $game): array
    {
        $msg = [];

        $user = $game->getUser();
        $userId = $user->getId();
        $ship = $wrapper->get();

        $msg[] = sprintf(
            "Das Energienetz um die %s in Sektor %s ist implodiert",
            $ship->getName(),
            $ship->getSectorString()
        );

        $damage_wrapper = new DamageWrapper(
            (int)ceil(rand(65, 85) * $wrapper->get()->getMaxHuell() / 100)
        );
        $damage_wrapper->setCrit(rand(0, 3) === 0);
        $damage_wrapper->setShieldDamageFactor(100);
        $damage_wrapper->setHullDamageFactor(100);
        $damage_wrapper->setIsPhaserDamage(true);

        $msg = array_merge($msg, $this->applyDamage->damage($damage_wrapper, $wrapper));

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

            $destroyMsg = $this->shipRemover->destroy($wrapper);
            if ($destroyMsg !== null) {
                $msg[] = $destroyMsg;
            }
        }

        return $msg;
    }
}
