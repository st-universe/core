<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;

final class ConstructionHubShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    public function __construct(
        ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository
    ) {
        $this->shipyardShipQueueRepository = $shipyardShipQueueRepository;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 20;
    }

    public function getEnergyConsumption(): int
    {
        return 10;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if (!$ship->hasEnoughCrew()) {
            $reason = _('ungenügend Crew vorhanden ist');
            return false;
        }

        return true;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB)->setMode(ShipSystemModeEnum::MODE_ON);
        $this->shipyardShipQueueRepository->restartQueueByShipyard($ship->getId());
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB)->setMode(ShipSystemModeEnum::MODE_OFF);
        $this->stopShipyardQeue($ship);
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $this->stopShipyardQeue($wrapper->get());
    }

    private function stopShipyardQeue(ShipInterface $ship): void
    {
        $this->shipyardShipQueueRepository->stopQueueByShipyard($ship->getId());
    }
}
