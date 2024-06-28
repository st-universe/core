<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use RuntimeException;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
    }

    public function refactor(): void
    {
        foreach ($this->shipRepository->findAll() as $ship) {

            $astroStartTurn = $ship->getAstroStartTurn();
            if ($astroStartTurn === null) {
                continue;
            }

            $astroLab = $this->shipWrapperFactory
                ->wrapShip($ship)
                ->getAstroLaboratorySystemData();

            if ($astroLab === null) {
                throw new RuntimeException('this should not happen');
            }

            $astroLab
                ->setAstroStartTurn($astroStartTurn)
                ->update();
        }
    }
}
