<?php

namespace Stu\Module\Maintenance;

use Stu\Orm\Repository\PirateWrathRepositoryInterface;

final class PirateWrathDecreaser implements MaintenanceHandlerInterface
{
    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository
    ) {
    }

    public function handle(): void
    {
        foreach ($this->pirateWrathRepository->findAll() as $pirateWrath) {

            $currentWrath = $pirateWrath->getWrath();
            if ($currentWrath <= 100) {
                continue;
            }

            $pirateWrath->setWrath(
                $currentWrath - 1
            );
            $this->pirateWrathRepository->save($pirateWrath);
        }
    }
}
