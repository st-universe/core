<?php

namespace Stu\Module\Maintenance;

use Stu\Orm\Entity\PirateWrathInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;

final class PirateWrathDecreaser implements MaintenanceHandlerInterface
{
    public const DECREASE_AMOUNT_PER_DAY = 10;

    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository
    ) {
    }

    public function handle(): void
    {
        foreach ($this->pirateWrathRepository->findAll() as $pirateWrath) {

            $currentWrath = $pirateWrath->getWrath();
            if ($currentWrath <= PirateWrathInterface::DEFAULT_WRATH) {
                continue;
            }

            $pirateWrath->setWrath(
                $currentWrath - self::DECREASE_AMOUNT_PER_DAY
            );
            $this->pirateWrathRepository->save($pirateWrath);
        }
    }
}
