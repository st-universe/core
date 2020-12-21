<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class WarpdriveShipSystem implements ShipSystemTypeInterface
{
    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function isAlreadyActive(ShipInterface $ship): bool
    {
        return $ship->getWarpState();
    }

    public function checkActivationConditions(ShipInterface $ship): bool
    {
        return $ship->getWarpState() === false &&
            ($ship->getTraktorShip() === null || $ship->traktorBeamFromShip());
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->cancelRepair();
        $ship->setDockedTo(null);
        $ship->setWarpState(true);

        if ($ship->traktorBeamFromShip()) {
            if ($ship->getEps() > $this->getEnergyUsageForActivation()) {
                $traktorShip = $ship->getTraktorShip();

                $traktorShip->cancelRepair();
                $traktorShip->setWarpState(true);

                $ship->setEps($ship->getEps() - $this->getEnergyUsageForActivation());

                $this->shipRepository->save($traktorShip);
            } else {
                $ship->deactivateTraktorBeam();
            }
        }
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setWarpState(false);

        if ($ship->traktorBeamFromShip()) {
            $traktorShip = $ship->getTraktorShip();

            $traktorShip->setWarpState(false);

            $this->shipRepository->save($traktorShip);
        }
    }
}
