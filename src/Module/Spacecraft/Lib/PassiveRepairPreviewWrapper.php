<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\Spacecraft;

final class PassiveRepairPreviewWrapper
{
    /**
     * @param array<ShipRepairCost> $repairCosts
     */
    public function __construct(
        private readonly SpacecraftWrapperInterface $wrapper,
        private readonly int $repairDuration,
        private readonly array $repairCosts
    ) {}

    public function get(): Spacecraft
    {
        return $this->wrapper->get();
    }

    public function getRepairDuration(): int
    {
        return $this->repairDuration;
    }

    public function getRepairDurationLabel(): string
    {
        $hours = intdiv($this->repairDuration, 3600);
        $minutes = intdiv($this->repairDuration % 3600, 60);

        if ($hours > 0) {
            return sprintf('%dh %02dm', $hours, $minutes);
        }

        if ($minutes > 0) {
            return sprintf('%dm', $minutes);
        }

        return sprintf('%ds', $this->repairDuration);
    }

    /**
     * @return array<ShipRepairCost>
     */
    public function getRepairCosts(): array
    {
        return $this->repairCosts;
    }
}
