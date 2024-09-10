<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;

final class CreateInterstellarMedia implements MaintenanceHandlerInterface
{
    public function __construct(private LocationMiningRepositoryInterface $locationMiningRepository) {}

    #[Override]
    public function handle(): void
    {
        $entries = $this->locationMiningRepository->findDepletedEntries();

        foreach ($entries as $entry) {
            $currentAmount = $entry->getActualAmount();
            $maxAmount = $entry->getMaxAmount();

            if ($currentAmount === 0) {
                $newAmount = max(1, (int) ceil($maxAmount * 0.05));
            } else {
                $newAmount = (int) ceil($currentAmount * 1.15);
            }

            if ($newAmount >= $maxAmount) {
                $newAmount = $maxAmount;
                $entry->setDepletedAt(null);
            }

            $entry->setActualAmount($newAmount);
            $this->locationMiningRepository->save($entry);
        }
    }
}
