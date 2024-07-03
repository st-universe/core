<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperProjectileWeapon;
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

            $wrapper = $this->shipWrapperFactory
                ->wrapShip($ship);

            $launcher = $wrapper
                ->getProjectileLauncherSystemData();

            if ($launcher === null) {
                continue;
            }

            $buildplan = $ship->getBuildplan();
            if ($buildplan === null) {
                continue;
            }

            $unit = new ModuleRumpWrapperProjectileWeapon(
                $ship->getRump(),
                $buildplan
            );

            $unit->apply($wrapper);
        }
    }
}
