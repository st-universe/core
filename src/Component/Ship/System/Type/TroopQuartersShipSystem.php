<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class TroopQuartersShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const int QUARTER_COUNT = 100;
    public const int QUARTER_COUNT_BASE = 300;

    public function __construct(private CrewRepositoryInterface $crewRepository)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS;
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
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
}
