<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
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
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        foreach ($ship->getCrewlist() as $crewAssignment) {
            if ($crewAssignment->getSlot() === null) {
                $this->crewRepository->delete($crewAssignment->getCrew());
            }
        }
    }

    public function getEnergyUsageForActivation(): int
    {
        return 5;
    }

    public function getPriority(): int
    {
        return ShipSystemTypeEnum::SYSTEM_PRIORITIES[ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS];
    }

    public function getEnergyConsumption(): int
    {
        return 5;
    }
}
