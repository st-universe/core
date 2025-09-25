<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class TroopQuartersShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public const int QUARTER_COUNT = 100;
    public const int QUARTER_COUNT_BASE = 300;

    public function __construct(private CrewRepositoryInterface $crewRepository) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TROOP_QUARTERS;
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        foreach ($wrapper->get()->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getSlot() === null) {
                $this->crewRepository->delete($crewAssignment->getCrew());
            }
        }
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 5;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 5;
    }

    #[Override]
    public function canBeActivatedWithInsufficientCrew(): bool
    {
        return true;
    }
}