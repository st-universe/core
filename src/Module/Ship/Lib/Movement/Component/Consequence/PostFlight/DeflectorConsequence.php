<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Battle\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Damage\ApplyFieldDamageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapFieldTypeInterface;

class DeflectorConsequence extends AbstractFlightConsequence
{
    private ApplyFieldDamageInterface $applyFieldDamage;

    public function __construct(
        ApplyFieldDamageInterface $applyFieldDamage
    ) {
        $this->applyFieldDamage = $applyFieldDamage;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        if ($wrapper->get()->isTractored()) {
            return;
        }

        $ship = $wrapper->get();
        $nextFieldType = $flightRoute->getNextWaypoint()->getFieldType();

        //Einflugschaden Feldschaden
        if ($nextFieldType->getSpecialDamage() > 0) {
            $this->applyFieldDamage->damage(
                $wrapper,
                $nextFieldType->getSpecialDamage(),
                true,
                sprintf(
                    _('%s in Sektor %d|%d'),
                    $nextFieldType->getName(),
                    $ship->getPosX(),
                    $ship->getPosY()
                ),
                $messages
            );

            if ($ship->isDestroyed()) {
                return;
            }
        }

        $energyCost = $nextFieldType->getEnergyCosts();
        if ($energyCost === 0) {
            return;
        }

        //check for deflector state
        $deflectorDestroyed = !$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);
        if ($deflectorDestroyed) {

            $this->applyFieldDamage->damage(
                $wrapper,
                $nextFieldType->getDamage(),
                false,
                'Deflektor außer Funktion.',
                $messages
            );

            return;
        }

        $hasEnoughEnergyForDeflector = $this->hasEnoughEpsForDeflector($wrapper, $nextFieldType);

        //Einflugschaden Energiemangel oder Deflektor zerstört
        if (!$hasEnoughEnergyForDeflector) {

            $this->applyFieldDamage->damage(
                $wrapper,
                $nextFieldType->getDamage(),
                false,
                'Nicht genug Energie für den Deflektor.',
                $messages
            );
        }
    }


    private function hasEnoughEpsForDeflector(
        ShipWrapperInterface $wrapper,
        MapFieldTypeInterface $nextFieldType
    ): bool {

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            return false;
        }

        $energyCost = $nextFieldType->getEnergyCosts();

        if ($epsSystem->getEps() < $energyCost) {
            $epsSystem->setEps(0)->update();
            return false;
        }

        $epsSystem->lowerEps($nextFieldType->getEnergyCosts())->update();

        return true;
    }
}
