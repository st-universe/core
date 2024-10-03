<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;

final class PirateWrathDecreaser implements MaintenanceHandlerInterface
{
    public const int DECREASE_AMOUNT_PER_DAY = 20;

    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository
    ) {}

    #[Override]
    public function handle(): void
    {
        foreach ($this->pirateWrathRepository->findAll() as $pirateWrath) {

            $currentWrath = $pirateWrath->getWrath();
            if ($currentWrath <= PirateWrathManager::DEFAULT_WRATH) {
                continue;
            }

            $pirateWrath->setWrath(
                $currentWrath - self::DECREASE_AMOUNT_PER_DAY
            );
            $this->pirateWrathRepository->save($pirateWrath);
        }
    }
}
