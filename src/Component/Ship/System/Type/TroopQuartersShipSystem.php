<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class TroopQuartersShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const QUARTER_COUNT = 100;
    public const QUARTER_COUNT_BASE = 300;

    private CrewRepositoryInterface $crewRepository;

    public function __construct(
        CrewRepositoryInterface $crewRepository
    ) {
        $this->crewRepository = $crewRepository;
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS;
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        foreach ($wrapper->get()->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getSlot() === null) {
                $this->crewRepository->delete($crewAssignment->getCrew());
            }
        }
    }

    public function getEnergyUsageForActivation(): int
    {
        return 5;
    }

    public function getEnergyConsumption(): int
    {
        return 5;
    }
}
