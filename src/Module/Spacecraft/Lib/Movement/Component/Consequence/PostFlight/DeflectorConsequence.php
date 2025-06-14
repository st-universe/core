<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Damage\ApplyFieldDamageInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\MapFieldTypeInterface;

class DeflectorConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(private ApplyFieldDamageInterface $applyFieldDamage) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

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

            if ($ship->getCondition()->isDestroyed()) {
                return;
            }
        }

        $energyCost = $nextFieldType->getEnergyCosts();
        if ($energyCost === 0) {
            return;
        }

        //check for deflector state
        $deflectorDestroyed = !$ship->isSystemHealthy(SpacecraftSystemTypeEnum::DEFLECTOR);
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
        SpacecraftWrapperInterface $wrapper,
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
