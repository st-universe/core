<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class WarpdriveShipSystem implements ShipSystemTypeInterface
{
    private $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
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
            if ($ship->getEps() >= $this->getEnergyUsageForActivation()) {
                $ship->getTraktorShip()->cancelRepair();
                $ship->getTraktorShip()->setWarpState(true);

                $ship->setEps($ship->getEps() - $this->getEnergyUsageForActivation());

                $this->shipRepository->save($ship->getTraktorShip());
            } else {
                $ship->deactivateTraktorBeam();
            }
        }
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->setWarpState(false);

        if ($ship->traktorBeamFromShip()) {
            $ship->getTraktorShip()->setWarpState(true);

            $this->shipRepository->save($ship->getTraktorShip());
        }
    }
}
