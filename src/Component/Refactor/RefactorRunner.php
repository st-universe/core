<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
    ) {}

    public function refactor(): void
    {
        foreach ($this->spacecraftRepository->findAll() as $spacecraft) {
            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

            $shieldSystemData = $wrapper->getShieldSystemData();
            if ($shieldSystemData !== null) {
                $shieldSystemData
                    ->setShieldRegenerationTimer($spacecraft->getShieldRegenerationTimer())
                    ->update();
            }

            $shieldSystemData = $wrapper->getLssSystemData();
            if ($shieldSystemData !== null) {
                $shieldSystemData
                    ->setSensorRange($spacecraft->getSensorRange())
                    ->update();
            }
        }
    }
}
