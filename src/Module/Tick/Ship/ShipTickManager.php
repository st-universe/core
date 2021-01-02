<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipTickManager implements ShipTickManagerInterface
{
    private ShipRemoverInterface $shipRemover;

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRemoverInterface $shipRemover,
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRemover = $shipRemover;
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
    }

    public function work(): void
    {
        foreach ($this->shipRepository->getPlayerShipsForTick() as $ship) {
            //echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";

            //handle ship only if vacation mode not active
            if (!$ship->getUser()->isVacationRequestOldEnough())
            {
                $this->shipTick->work($ship);
            }

        }
        $this->handleNPCShips();
        $this->removeEmptyEscapePods();
        $this->lowerTrumfieldHuell();
    }

    private function removeEmptyEscapePods(): void
    {
        foreach ($this->shipRepository->getEscapePods() as $ship) {
            if ($ship->getCrewCount() == 0) {
                $this->shipRemover->remove($ship);
                continue;
            }
        }
    }

    private function lowerTrumfieldHuell(): void
    {
        foreach ($this->shipRepository->getDebrisFields() as $ship) {
            $lower = rand(5, 15);
            if ($ship->getHuell() <= $lower) {
                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHuell() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function handleNPCShips(): void
    {
        // @todo
        foreach ($this->shipRepository->getNpcShipsForTick() as $ship) {
            $eps = (int)ceil($ship->getMaxEps() / 10);
            if ($eps + $ship->getEps() > $ship->getMaxEps()) {
                $eps = $ship->getMaxEps() - $ship->getEps();
            }
            $ship->setEps($ship->getEps() + $eps);

            $this->shipRepository->save($ship);
        }
    }
}
