<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Orm\Entity\PirateWrathInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;

final class PirateWrathDecreaser implements MaintenanceHandlerInterface
{
    public const int DECREASE_AMOUNT_PER_DAY = 20;

    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository
    ) {
    }

    #[Override]
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
