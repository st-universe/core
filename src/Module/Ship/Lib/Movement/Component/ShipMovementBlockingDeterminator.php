<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Component\Ship\System\Data\EpsSystemData;

/**
 * Looks for reasons a certain ship cannot move from its position
 */
final class ShipMovementBlockingDeterminator implements ShipMovementBlockingDeterminatorInterface
{
    public function determine(array $wrappers): array
    {
        $reasons = [];

        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();

            // zu wenig Crew
            if (!$ship->hasEnoughCrew()) {
                $reasons[] = sprintf(
                    'Die %s hat ungenügend Crew',
                    $ship->getName()
                );

                continue;
            }

            /** @var EpsSystemData $epsSystem */
            $epsSystem = $wrapper->getEpsSystemData();

            $energyCostPerField = $ship->getRump()->getFlightEcost();
            $tractorBeamTarget = $ship->getTractoringShip();
            $shipEnergyStorage = $epsSystem->getEps();
            $warpdrivesystem = $wrapper->getWarpDriveSystemData();

            if ($tractorBeamTarget !== null) {
                $tractoredShipEnergyCostPerField = $tractorBeamTarget->getRump()->getFlightEcost() + $energyCostPerField;

                if ($shipEnergyStorage < $tractoredShipEnergyCostPerField) {
                    $reasons[] = sprintf(
                        'Die %s hat nicht genug Energie für den Traktor-Flug (%d benötigt)',
                        $ship->getName(),
                        $tractoredShipEnergyCostPerField
                    );

                    continue;
                }
            }

            // zu wenig E zum weiterfliegen
            if ($shipEnergyStorage < $energyCostPerField) {
                $reasons[] = sprintf(
                    'Die %s hat nicht genug Energie für den Flug (%d benötigt)',
                    $ship->getName(),
                    $energyCostPerField
                );
            }

            // zu wenig Warpantriebsenergie zum weiterfliegen
            if ($warpdrivesystem->getWarpDrive() < 1) {
                $reasons[] = sprintf(
                    'Die %s hat nicht genug Warpantriebsenergie für den Flug',
                    $ship->getName()
                );
            }
        }

        return $reasons;
    }
}
